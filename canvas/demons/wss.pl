#!/usr/bin/perl
use Cwd;
use JSON;
use IO::Select;
use Data::Dumper;
use Proc::Daemon;
use IO::Socket::INET;
use Encode qw/encode decode/;
use Protocol::WebSocket::Frame;
use Protocol::WebSocket::Handshake::Server;
use constant DEBUG => 1;

my $json = JSON->new->allow_nonref;
my $demon = Proc::Daemon->new(work_dir     => getcwd,
                              pid_file     => '/var/run/wss.pid',                            
							  child_STDOUT => '+>>log.log',
                              child_STDERR => '+>>debug.txt',
                             );
my $pid = $demon->Init;
my $db;

sub log {
	open (LOG, '>>wss.log'); 
	my ($msg,$errLevel) = @_;	
	my $t  = localtime time;
	print LOG "[".$t."]\t".$msg."\n" if DEBUG >= $errLevel;
	close(LOG);
	return 1;
}
sub captureSIG {
	my $sig = shift;
	&log("Capture signal: $sig $! $?",1);
	exit(1);
}
sub try (&$) {
	my ($try,$catch) = @_;
	eval $try;
	if ($@) {
		local $_ = $@;
		&$catch;
	}
}
sub catch (&) { $_[0]; }

foreach my $s (keys %SIG) {
	if ($s eq PIPE) { $SIG{$s} = 'IGNORE'; } 
	else { $SIG{$s} = \&captureSIG; }
}

unless ($pid) {
# init na socketa
	my $service_sock = IO::Socket::INET->new (			
									 #"LocalAddr"	=> '0.0.0.0',
									 "LocalPort" 	=> 7000,
									 "Listen" 	=> 1,
									 "Reuse" 	=> 1,
									 "Proto" 	=> "tcp"
									);
	die &log("Service soket ne moze da byde syzdaden. Prichina: $!") unless($service_sock);
	$service_sock->blocking(1);

	my $client_sock = IO::Socket::INET->new (#"LocalHost" => '213.91.252.143',
									"LocalPort"     => 7001,
									"Listen"        => 100,
									"Reuse"         => 1,
									"Proto"         => "tcp",
									"Type"          => SOCK_STREAM,
									"Timeout"       => 3
								    );
	die &log("Client soket ne moze da byde syzdaden. Prichina: $!") unless($client_sock);
	$client_sock->blocking(0);
	
	my $readySocks = new IO::Select();
	$readySocks->add($service_sock);
	$readySocks->add($client_sock);	
	my %HS = ();
	my %FR = ();
	my %clientSocket;
	my $sSock;
	my $sockPort;
	my $sockAddr;
    while (@ready = $readySocks->can_read) {
        foreach $sock (@ready) {                                    
            if ($sock == $client_sock) {				
				&log("CL connected: \n",3);
                $readySocks->add($client_sock->accept());                
            } elsif ($sock == $service_sock) {
                $readySocks->add($service_sock->accept());   
				&log("ME connected: \n",5);
				$sSock = $sock->sockname(); 
				$sockPort = $sock->sockport();                           
				$sockAddr = $sock->sockaddr();                   
            } else {				
				my $recv ="";													
				if ($sockAddr == $sock->sockaddr() && $sockPort == $sock->sockport()) {
					$recv = "";
					my $cn = 0;
					while (<$sock>) {						
						$cn++;
						if ($cn==1) {
							my ($type,$target,$data) = split(/\t/,$_);							
							chomp($type);
							&log("TYPE:\t$type",5);
							if ($type ne "session" && $type ne "region") {
								&log("PORT INTRUDER0:\t".$sock->peerhost()." : ".$sock->peerport()."\tRECV:\t$_",1);
								$readySocks->remove($sock);
								$sock->shutdown(2);
								close($sock);
								last;
							}
						}
						$recv.=$_; 
					}						
					&log("PHP DATA RECV:\t".length($recv),5);
				} else {										
					my $r = sysread($sock,$recv,40960);
				}        				
                if (length($recv)) { 
					my @packets = split(/\n/,$recv);						
					if ($sockAddr == $sock->sockaddr() && $sockPort == $sock->sockport()) {    															
					 	foreach my $pack (@packets) {							
							&log("PKG:\t$pack\n",5);
							my ($type,$target,$data) = split(/\t/,$pack);
							chomp($type);
							if ($type eq 'session') {
								&log("PKG BY SESSION: $data\n",5);
								my $css = $clientSocket{$target}{'socket'};								
								$css->syswrite($FR{$css}->new($data)->to_bytes) if $css;
							} elsif ($type eq 'region') {
								&log("PKG BY REGION: $data\n",5);								
								my @regions = split(/,/,$target);
						sess:   foreach my $sess (keys %clientSocket) {
									foreach my $region (@regions) {
										if ($clientSocket{$sess}{'regions'}{$region}) {
											my $csr = $clientSocket{$sess}{'socket'};
											$csr->syswrite($FR{$csr}->new($data)->to_bytes) if $FR{$csr} && $csr;
											next sess;
										}
									}
								}								
							}
						}										
					} else {											
						foreach my $pack (@packets) {								
							my ($type,$target,$data) = split(/\t/,$pack);							
							if ($type eq 'auth') {
								&log("AUTH RECV: $data\t$target",1);
								$clientSocket{$target}{'regions'} = $json->decode($data);
								$clientSocket{$target}{'confirmed'} = false;
								$clientSocket{$target}{'time'} = time();
								&log('%clientSocket{\n'.Data::Dumper->Dump([%clientSocket]).'}',5);								
							}						
						}							
						(!$HS{$sock}) && ($HS{$sock} = Protocol::WebSocket::Handshake::Server->new);
						(!$FR{$sock}) && ($FR{$sock} = Protocol::WebSocket::Frame->new);						
						if (!$HS{$sock}->is_done) {		
							$HS{$sock}->parse($recv);							
							if ($HS{$sock}->is_done) {								
								$sock->syswrite($HS{$sock}->to_string);
								&log("HandShake complete.\t".$HS{$sock}->to_string."\n",5);
							} else {				
								&log('fail HS\n',5);
								delete $HS{$sock};
								delete $FR{$sock};
							}												
							next;
						}								
										
						if ($HS{$sock}->error) { &log("HSErr: \t".$HS{$sock}->error,1) } 									
						$FR{$sock}->append($recv); #chetem msg ot klienta										    
						while (my $msg = $FR{$sock}->next) {
							my ($type,$sess,$user) = split("\t",$msg);
							if ($type eq "init") {
								&log("INIT: $user\t$sess\n",1); 
								if (exists $clientSocket{$sess}  && length($clientSocket{$sess}{'regions'})) {
									&log("CONFIRM AUTH: $user\t$sess\n",1); 
									$clientSocket{$sess}{'confirmed'} = true;
									$clientSocket{$sess}{'socket'} = $sock;	
									$clientSocket{$sess}{'socname'} = $sock->sockname();
									my %confirm = ('confirmauth'=>true);
									$sock->syswrite($FR{$sock}->new(encode_json(\%confirm))->to_bytes);
								} else {
									&log("REJECT AUTH: $user\t$sess\n",1); 
									$readySocks->remove($sock);	
									delete $clientSocket{$sess};
									delete $FR{$sock};
									delete $HS{$sock};									
									$sock->shutdown(2);
									close($sock);
									last;
								}
							} elsif ($type eq "close") {								
								if ($clientSocket{$sess}{'time'} < time()-2) {
									&log("CLEAN CLOSE: $user\t$sess\n",1); 
									$readySocks->remove($sock);
									delete $clientSocket{$sess};
									delete $FR{$sock};
									delete $HS{$sock};									
									$sock->shutdown(2);
									close($sock);																
								} else {
									$readySocks->remove($sock);
									delete $HS{$sock};
									delete $FR{$sock};
									$sock->shutdown(2);
									close($sock);	
								}
								last;
							}							
						}
					}
                } else {                   
                    $readySocks->remove($sock);
					foreach my $sess (keys %clientSocket) {
						if($clientSocket{$sess}{'socket'} eq $sock) { # && $clientSocket{$sess}{'time'} < time()-5) {   # && !$clientSocket{$sess}{'confirmed'} && $clientSocket{$sess}{'time'}<time()-60) {
							delete $clientSocket{$sess}; #if $clientSocket{$sess}{'socket'} eq $sock;
							delete $HS{$sock};
							delete $FR{$sock};
							&log("FORCE CLOSE: $sess",1);
						}
					}
                    $sock->shutdown(2);
                    close($sock);                   
                }
            }
        }
    }
    exit 0;
}


