// Скрипт, който показжа на сървъра че дадеден потребител е ONLINE

//API функцията, която обработва Задачите
var api_online = "api/user_online.php"
var id_online_user = 0;
var online_time_loop = 2000;

function UserOnlineLoop() 
{
	if (!window.XMLHttpRequest) { 
		window.XMLHttpRequest = function() { 
			var xmlHttp; 
			try { 
				xmlHttp = new ActiveXObject("Msxml2.XMLHTTP.4.0"); 
				return xmlHttp;
			} 
			catch (ex) {}
			try { 
				xmlHttp = new ActiveXObject("MSXML2.XMLHTTP"); 
				return xmlHttp;
			} 
			catch (ex){}
			try { 
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP"); 
				return xmlHttp;
			} 
			catch (ex) {}
			return null;
		}
	}
	if (XMLHttpRequest) {
		xmlHttp = new XMLHttpRequest(); 
		xmlHttp.onreadystatechange = function()
		url=api_online+"?id_user="+id_online_user+"&period="+online_time_loop;

		xmlHttp.open("GET", url, true);
		xmlHttp.send(null);
	}

	setTimeout("UserOnlineLoop()", online_time_loop);
	return true;
}
