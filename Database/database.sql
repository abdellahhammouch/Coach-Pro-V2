create database coach_pro;
use coach_pro;

create table users (
    id_user int auto_increment primary key,
    nom_user varchar(100),
    prenom_user varchar(100),
    email_user varchar(150) unique,
    role_user enum('coach', 'sportif') not null
);

create table coachs (
    id_user int primary key,
    discipline_coach varchar(100),
    experiences_coach int,
    description_coach text,
    foreign key (id_user) references users(id_user)
);

create table sportifs (
    id_user int primary key,
    foreign key (id_user) references users(id_user)
);

create table seances (
    id_seance int auto_increment primary key,
    coach_id int,
    date_seance date,
    heure_seance time,
    duree_senace int,
    statut_seance enum('disponible', 'reservee') default 'disponible',
	foreign key (coach_id) references coachs(id_user)
);