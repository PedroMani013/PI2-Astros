CREATE DATABASE astros;
USE astros;

-- Tabela de Administradores
CREATE TABLE tb_administradores (
	idadmin INT PRIMARY KEY AUTO_INCREMENT,
	nome VARCHAR(80) NOT NULL,
	email VARCHAR(60) NOT NULL,
	senha VARCHAR(16) NOT NULL
);

-- Tabela de Votações
CREATE TABLE tb_votacoes (
	idvotacao INT PRIMARY KEY AUTO_INCREMENT,
	curso VARCHAR(90) NOT NULL,
	semestre INT NOT NULL,
	ativa VARCHAR(3) NOT NULL,
	data_inicio DATETIME NOT NULL,
	data_candidatura DATETIME NOT NULL,
	data_final DATETIME NOT NULL,
	idadmin INT,
	FOREIGN KEY (idadmin) REFERENCES tb_administradores(idadmin)
	idcandidato_representante INT NULL,
	FOREIGN KEY (idcandidato_representante) REFERENCES tb_candidatos(idcandidato),
	idcandidato_suplente INT NULL,
	FOREIGN KEY (idcandidato_suplente) REFERENCES tb_candidatos(idcandidato);
);

-- Tabela de Alunos
CREATE TABLE tb_alunos (
	idaluno INT PRIMARY KEY AUTO_INCREMENT,
	nome VARCHAR(80) NOT NULL,
	ra VARCHAR(13) NOT NULL,
	email VARCHAR(60) NOT NULL,
	senha VARCHAR(16) NOT NULL,
	curso VARCHAR(90) NOT NULL,
	semestre INT NOT NULL,
	idvotacao INT,
	FOREIGN KEY (idvotacao) REFERENCES tb_votacoes(idvotacao)
);

-- Tabela de Candidatos
CREATE TABLE tb_candidatos (
	idcandidato INT PRIMARY KEY AUTO_INCREMENT,
	imagem BLOB,
	nomealuno VARCHAR(80) NOT NULL,
    email VARCHAR(60) NOT NULL,
    ra VARCHAR(13) NOT NULL,
	idvotacao INT,
	FOREIGN KEY (idvotacao) REFERENCES tb_votacoes(idvotacao)
);

-- Tabela de Votos (Rastreia quem votou e em quem)
CREATE TABLE tb_votos (
	idvoto INT PRIMARY KEY AUTO_INCREMENT,
	datavoto DATETIME NOT NULL,
	idaluno INT NOT NULL,
	FOREIGN KEY (idaluno) REFERENCES tb_alunos(idaluno),
	idcandidato INT NOT NULL,
	FOREIGN KEY (idcandidato) REFERENCES tb_candidatos(idcandidato)
);

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO tb_candidatos (idcandidato, nomealuno, ra, email)
VALUES (0, 'VOTO NULO', '0000000000000', 'nulo@sistema.local');

SET SQL_MODE = '';

-- Inserção de Administradores
INSERT INTO tb_administradores (nome, email, senha)
VALUES
	('Alexandre da silva', 'alexandre.silva02@fatec.sp.gov.br', 'alesiva');

-- Inserção de Alunos (idvotacao corrigido conforme o curso)
INSERT INTO tb_alunos (nome, ra, email, senha, curso, semestre, idvotacao)
VALUES

-- 10 Gestão Empresarial
('Lucas Ferreira', "2780642613001", 'lucas.ferreira@fatec.sp.gov.br', 'Lf8921aa', 'Gestão Empresarial', 1, NULL),
('Mariana Duarte', "2780642613002", 'mariana.duarte@fatec.sp.gov.br', 'Md4472bb', 'Gestão Empresarial', 1, NULL),
('Ricardo Alves', "2780642613003", 'ricardo.alves@fatec.sp.gov.br', 'Ra7315cc', 'Gestão Empresarial', 1, NULL),
('Juliana Campos', "2780642613004", 'juliana.campos@fatec.sp.gov.br', 'Jc1289dd', 'Gestão Empresarial', 1, NULL),
('Gabriel Manrique', "2780642613005", 'gabriel.manrique@fatec.sp.gov.br', '12345678', 'Gestão Empresarial', 1, NULL),
('Ana Ribeiro', "2780642613006", 'ana.ribeiro@fatec.sp.gov.br', 'Ar8744ff', 'Gestão Empresarial', 1, NULL),
('Thiago Moreira', "2780642613007", 'thiago.moreira@fatec.sp.gov.br', 'Tm2248gg', 'Gestão Empresarial', 1, NULL),
('Patricia Gomes', "2780642613008", 'patricia.gomes@fatec.sp.gov.br', 'Pg6012hh', 'Gestão Empresarial', 1, NULL),
('Felipe Cardoso', "2780642613009", 'felipe.cardoso@fatec.sp.gov.br', 'Fc9150ii', 'Gestão Empresarial', 1, NULL),
('Beatriz Moura', "2780642613010", 'beatriz.moura@fatec.sp.gov.br', 'Bm3782jj', 'Gestão Empresarial', 1, NULL),

-- 10 Desenvolvimento de Software Multiplataforma
('Carlos Nogueira', "2781392613001", 'carlos.nogueira@fatec.sp.gov.br', 'Cn1287kk', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Luis Porfirio', "2781392613002", 'luis.porfirio@fatec.sp.gov.br', '12345678', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Henrique Costa', "2781392613003", 'henrique.costa@fatec.sp.gov.br', 'Hc6610mm', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Natália Silva', "2781392613004", 'natalia.silva@fatec.sp.gov.br', 'Ns3118nn', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Rafael Rocha', "2781392613005", 'rafael.rocha@fatec.sp.gov.br', 'Rr7094oo', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Isabela Castro', "2781392613006", 'isabela.castro@fatec.sp.gov.br', 'Ic2046pp', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Bruno Teixeira', "2781392613007", 'bruno.teixeira@fatec.sp.gov.br', 'Bt7745qq', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Camila Rezende', "2781392613008", 'camila.rezende@fatec.sp.gov.br', 'Cr5580rr', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Eduardo Lima', "2781392613009", 'eduardo.lima@fatec.sp.gov.br', 'El1439ss', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Vanessa Araujo', "2781392613010", 'vanessa.araujo@fatec.sp.gov.br', 'Va8297tt', 'Desenvolvimento de Software Multiplataforma', 1, NULL),

-- 10 Gestão de Produção Industrial
('João Figueiredo', "2781622613001" , 'joao.figueiredo@fatec.sp.gov.br', 'Jf5001uu', 'Gestão de Produção Industrial', 1, NULL),
('Larissa Barros', "2781622613002" , 'larissa.barros@fatec.sp.gov.br', 'Lb3390vv', 'Gestão de Produção Industrial', 1, NULL),
('Matheus Prado', "2781622613003" , 'matheus.prado@fatec.sp.gov.br', 'Mp4682ww', 'Gestão de Produção Industrial', 1, NULL),
('Pedro Fernandes', "2781622613004" , 'pedro.fernandes@fatec.sp.gov.br', '12345678', 'Gestão de Produção Industrial', 1, NULL),
('André Pires', "2781622613005" , 'andre.pires@fatec.sp.gov.br', 'Ap1109yy', 'Gestão de Produção Industrial', 1, NULL),
('Helena Batista', "2781622613006" , 'helena.batista@fatec.sp.gov.br', 'Hb4470zz', 'Gestão de Produção Industrial', 1, NULL),
('Rogério Simões', "2781622613007" , 'rogerio.simoes@fatec.sp.gov.br', 'Rs9031aa', 'Gestão de Produção Industrial', 1, NULL),
('Sabrina Lopes', "2781622613008" , 'sabrina.lopes@fatec.sp.gov.br', 'Sl2608bb', 'Gestão de Produção Industrial', 1, NULL),
('Diego Barcellos', "2781622613009" , 'diego.barcellos@fatec.sp.gov.br', 'Db7764cc', 'Gestão de Produção Industrial', 1, NULL),
('Tatiane Moraes', "2781622613010" , 'tatiane.moraes@fatec.sp.gov.br', 'Tm8420dd', 'Gestão de Produção Industrial', 1, NULL);