--  Cinema Project Database
--  Created for PBA-WD Exam Project 2025

drop database if exists cinema;
create database cinema
  character set utf8mb4
  collate utf8mb4_unicode_ci;
use cinema;

-- users
create table user (
    user_id int auto_increment primary key,
    name varchar(100) not null,
    email varchar(200) not null unique,
    password_hash varchar(255) not null,
    is_admin tinyint(1) not null default 0
) engine=innodb;

-- movies
create table movie (
    movie_id int auto_increment primary key,
    title varchar(200) not null,
    duration_min int not null,
    release_year int,
    poster_url varchar(255),
    description text,
    genre varchar(50)
) engine=innodb;

-- halls
create table hall (
    hall_id int auto_increment primary key,
    name varchar(100) not null
) engine=innodb;

-- showings
create table showing (
    showing_id int auto_increment primary key,
    movie_id int not null,
    hall_id int not null,
    start_time datetime not null,
    price decimal(6,2) not null,
    unique key unique_hall_time (hall_id, start_time),
    constraint fk_showing_movie foreign key (movie_id) references movie(movie_id) on delete restrict on update cascade,
    constraint fk_showing_hall foreign key (hall_id) references hall(hall_id) on delete restrict on update cascade
) engine=innodb;

-- reservations
create table reservation (
    reservation_id int auto_increment primary key,
    user_id int not null,
    showing_id int not null,
    seat_list varchar(255) not null,
    created_at datetime not null default current_timestamp,
    constraint fk_reservation_user foreign key (user_id) references user(user_id) on delete cascade on update cascade,
    constraint fk_reservation_show foreign key (showing_id) references showing(showing_id) on delete cascade on update cascade,
    index idx_reservation_showing_id (showing_id),
    index idx_reservation_user_id (user_id)
) engine=innodb;

-- news
create table news (
    news_id int auto_increment primary key,
    title varchar(200) not null,
    body text not null,
    created_at datetime not null,
    user_id int not null,
    constraint fk_news_user foreign key (user_id) references user(user_id) on delete restrict on update cascade,
    index fk_news_user (user_id)
) engine=innodb;

-- company info
create table companyinfo (
    info_id int auto_increment primary key,
    about text not null,
    contact_email varchar(200) not null,
    contact_number varchar(50),
    opening_hours text not null,
    address varchar(255),
    hero_movie_id int
) engine=innodb;

-- reservation log
create table reservationlog (
    log_id int auto_increment primary key,
    reservation_id int,
    action varchar(50) not null,
    log_time datetime not null default current_timestamp
) engine=innodb;

-- triggers
delimiter $$

create trigger before_news_insert
before insert on news
for each row
begin
    if new.created_at is null or new.created_at = '0000-00-00 00:00:00' then
        set new.created_at = now();
    end if;
end$$

create trigger after_reservation_insert
after insert on reservation
for each row
begin
    insert into reservationlog (reservation_id, action)
    values (new.reservation_id, 'created');
end$$

delimiter ;

-- views
create view view_upcomingshows as
select 
    s.showing_id,
    m.title as movie_title,
    m.poster_url as movie_poster,
    s.start_time,
    s.price,
    h.name as hall_name
from showing s
join movie m on s.movie_id = m.movie_id
join hall h on s.hall_id = h.hall_id
where s.start_time >= now()
order by s.start_time asc;

create view view_userreservations as
select 
    r.reservation_id,
    u.user_id,
    u.name as user_name,
    u.email as user_email,
    m.title as movie_title,
    s.start_time as show_time,
    r.seat_list,
    r.created_at as reserved_at
from reservation r
join user u on r.user_id = u.user_id
join showing s on r.showing_id = s.showing_id
join movie m on s.movie_id = m.movie_id
order by r.created_at desc;

-- sample data
insert into user (name, email, password_hash, is_admin) values
('cinema admin', 'admin@cinema.local', '$2y$10$examplehashedpasswordadmin', 1),
('admin', 'admin@kino.cz', '$2y$10$Leb/yN5fzNvx2VZ1YaqFEupKcVji.JVt0OZbgFLA1xL.XG9o3LBnG', 1),
('patrik sevcik', 'pataSevcik@seznam.cz', '$2y$10$edD7y9WJR2ExcEssz0nsNOSPCKcwptijL7LPidd3Hs4gljLNQBp3W', 0),
('pepazdepa', 'pepa@gmail.com', '$2y$10$oYUP/ot3pOaLa4bb7MsUKezUVZslSjoR20lOc5ARKeGOCe5T18Yiq', 0);

insert into companyinfo (about, contact_email, contact_number, opening_hours, address, hero_movie_id) values
('small cinema with focus on quality movies and cozy atmosphere.', 'ourcinema@gmail.com', '+45 12 34 56 78', 'mon–fri: 10:00–22:00, sat–sun: 12:00–23:00', '123 cinema street, copenhagen, denmark', 2);

insert into movie (title, duration_min, release_year, poster_url, description, genre) values
('inception', 148, 2010, 'inception.jpg', 'sci-fi about dreams inside dreams.', 'sci-fi'),
('interstellar', 169, 2014, 'interstellar.jpg', 'travel through space and time.', 'sci-fi'),
('the dark knight', 152, 2008, 'dark-knight.jpg', 'batman fights joker.', 'action'),
('matrix', 136, 1999, 'matrix.jpg', 'neo objeví pravdu...', 'action'),
('cars', 117, 2006, 'cars.jpg', 'lightning mcqueen is known for saying "ka-chow!" piston cup.', 'cartoon');

insert into hall (name) values
('hall a'),
('hall b');

insert into showing (movie_id, hall_id, start_time, price) values
(2, 2, '2025-11-07 15:32:48', 130.00),
(3, 1, '2025-11-08 12:32:48', 110.00),
(3, 1, '2025-11-08 18:00:00', 120.00),
(4, 2, '2025-11-08 14:00:00', 130.00),
(4, 1, '2025-11-08 14:00:00', 130.00),
(4, 2, '2025-11-08 18:00:00', 130.00),
(4, 2, '2025-11-08 21:00:00', 130.00),
(4, 1, '2025-11-09 21:00:00', 130.00),
(3, 2, '2025-11-09 14:00:00', 130.00),
(3, 1, '2025-11-09 14:00:00', 130.00),
(3, 2, '2025-11-09 18:00:00', 130.00),
(3, 1, '2025-11-09 18:00:00', 130.00),
(3, 2, '2025-11-09 21:00:00', 130.00),
(2, 1, '2025-11-10 18:00:00', 130.00),
(2, 2, '2025-11-10 21:00:00', 130.00),
(2, 1, '2025-11-10 21:00:00', 130.00),
(1, 2, '2025-11-10 14:00:00', 130.00),
(1, 1, '2025-11-10 14:00:00', 130.00),
(1, 2, '2025-11-10 18:00:00', 130.00),
(5, 2, '2025-11-10 17:00:00', 150.00);

insert into news (title, body, created_at, user_id) values
('new premiere this week!', 'we have special pre-release screening on friday with short intro by director.', '2025-11-07 12:32:48', 1),
('weekend marathon: christopher nolan edition', 'this weekend we screen all nolan movies with 15% discount on popcorn. don\'t miss the epic finale on sunday!<br>schedule: inception (fri 20:00), interstellar (sat 19:00), dark knight (sun 20:30).', '2025-11-08 10:00:00', 1),
('student discount extended!', 'all students get 20% off on tickets every wednesday. just show your valid student id at the counter. offer valid until end of semester.', '2025-11-07 09:15:33', 1),
('special guest: film director q&a', 'join us this thursday after the 19:00 screening for exclusive q&a with local film director about indie filmmaking. free entry for ticket holders!', '2025-11-06 16:45:00', 1),
('christmas movie month starts soon!!!', 'december is coming with classic christmas movies every evening. vote for your favorite on our facebook page. most requested films will be screened!', '2025-11-09 11:30:00', 1),
('test article', 'this is just test article', '2025-11-09 17:07:11', 3);

insert into reservation (user_id, showing_id, seat_list) values
(4, 10, 'h12,h11'),
(5, 10, 'h10,g11');