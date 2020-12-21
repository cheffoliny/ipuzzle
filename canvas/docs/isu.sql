# ---- Àğõèâ -----
select * from sod_real.alarm_register order by id desc limit 10;
select * from sod_real.alarm_history; where id_alarm_register=1;
select * from sod_real.alarm_patruls where id_alarm_register=1;
select * from sod_real.alarm_reasons;

select id, num, name, geo_lat, geo_lan from sod_real.objects where id=36013575;
select * from sod_real.messages where id_obj=36013575;

select id, num, name, geo_lat, geo_lan from sod_real.objects where id=2033;
select * from sod_real.messages where id_obj=2033;


# ---- Èíèöèàëèçàöèÿ ----
update auto_real.auto set reaction_status = 0, reaction_object = 0, reaction_time="0000-00-00 00:00:00";
update sod_real.objects set reaction_status=0, reaction_car=0;
update auto_real.auto set geo_lat=43.2650941397687, geo_lan=26.9292821311951, geo_time=now()  where id=151; #25
update auto_real.auto set geo_lat=43.2622941397687, geo_lan=26.9220821311951, geo_time=now()  where id=81;  #21
truncate table `sod_real`.alarm_history;
truncate table `sod_real`.alarm_patruls;
truncate table `sod_real`.alarm_register;

#  oáåêò "Îôèñ Àé òè"
# 43.2736941397687 26.9267821311951
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(7256772, now(), 4992, 1, "ÎÁÙÀ ÀËÀĞÌÀ", 1) ;
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(7256768, now(), 4992, 1, "ÑÍÅÌÀÍÅ ÎÒ ÎÕĞÀÍÀ", 1) ;
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(7256770, now(), 4992, 1, "ÎÒÏÀÄÀÍÅ ÍÀ 220V", 1) ;

#  oáåêò "Êúùà Òåëåïîë"
# 43.2776525048828 26.9381573796272
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(13295, now(), 600, 1, "ÎÁÙÀ ÀËÀĞÌÀ", 1) ;
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(12965, now(), 600, 1, "ÑÍÅÌÀÍÅ ÎÒ ÎÕĞÀÍÀ", 1) ;
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(5048899, now(), 600, 1, "ÏÓÑÍÈ ÊËÈÌÀÒÈÊÀ Â ÑÚĞÂÚĞÍÎ", 1) ;
insert into sod_real.archiv_201102 (id_msg, msg_time, num, status, msg, alarm) values(13679, now(), 600, 1, "ÑÍÅÌÀÍÅ ÎÒ ÎÕĞÀÍÀ", 1) ;


# ---- Àâòîìîáèëè --------

# H 0668 BA - 25 - ID 151
select id, reg_num, geo_lat, geo_lan, geo_time, reaction_status from auto_real.auto where id=151;
                             #    43.2736941397687          26.9267821311951
update auto_real.auto set geo_lat=43.2668141397687, geo_lan=26.9292821311951, geo_time=now()  where id=151;
update auto_real.auto set geo_lat=43.2685341397687, geo_lan=26.9287821311951, geo_time=now()  where id=151;
update auto_real.auto set geo_lat=43.2650941397687, geo_lan=26.9282821311951, geo_time=now()  where id=151;
update auto_real.auto set geo_lat=43.2702541397687, geo_lan=26.9277821311951, geo_time=now()  where id=151;
update auto_real.auto set geo_lat=43.2719741397687, geo_lan=26.9272821311951, geo_time=now()  where id=151;
update auto_real.auto set geo_lat=43.2733941397687, geo_lan=26.9267821311951, geo_time=now()  where id=151;
update auto_real.auto set geo_lat=43.2735941397687, geo_lan=26.9267821311951, geo_time=now()  where id=151;

update auto_real.auto set geo_lat=43.2736941397687, geo_lan=26.9267821311951, geo_time=now()  where id=151;

update auto_real.auto set geo_lat=43.2776525048828, geo_lan=26.9381573796272, geo_time=now()  where id=151;

# H3394 - 21 - ID 81
select id, reg_num, geo_lat, geo_lan, geo_time, reaction_status from auto_real.auto where id=81;
                             #    43.2776525048828          26.9381573796272
update auto_real.auto set geo_lat=43.2653658127915, geo_lan=26.9252971808815, geo_time=now()  where id=81;
update auto_real.auto set geo_lat=43.2684374858143, geo_lan=26.9285122305679, geo_time=now()  where id=81;
update auto_real.auto set geo_lat=43.2715091588371, geo_lan=26.9317272802544, geo_time=now()  where id=81;
update auto_real.auto set geo_lat=43.2761166683714, geo_lan=26.9365498547840, geo_time=now()  where id=81;
update auto_real.auto set geo_lat=43.2775625048828, geo_lan=26.9381573796272, geo_time=now()  where id=81;

update auto_real.auto set geo_lat=43.2776525048828, geo_lan=26.9381573796272, geo_time=now()  where id=81;

alter table archiv_201102 auto_increment=1;

select * from archiv_201102;

#Úï íà êîîğäèíàòèòå
SELECT CONCAT("UPDATE auto set geo_lan=",geo_lan, ", geo_lat=", geo_lat, ", geo_time='", geo_time, "', geo_real_time='", geo_real_time,"' where reg_num='", reg_num, "';") FROM auto; 
