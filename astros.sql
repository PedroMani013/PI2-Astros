CREATE DATABASE astros;
USE astros;

CREATE TABLE tb_administradores (
    idadmin INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    email VARCHAR(60) NOT NULL,
    senha VARCHAR(16) NOT NULL
);

CREATE TABLE tb_votacoes (
    idvotacao INT PRIMARY KEY AUTO_INCREMENT,
    curso VARCHAR(90) NOT NULL,
    semestre INT NOT NULL,
    ativa VARCHAR(3) NOT NULL,
    data_inicio DATETIME NOT NULL,
    data_candidatura DATETIME NOT NULL,
    data_final DATETIME NOT NULL,
    idadmin INT,
    idcandidato_representante INT NULL,
    idcandidato_suplente INT NULL,
    FOREIGN KEY (idadmin) REFERENCES tb_administradores(idadmin)
);

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

CREATE TABLE tb_candidatos (
    idcandidato INT PRIMARY KEY AUTO_INCREMENT,
    imagem BLOB,
    nomealuno VARCHAR(80) NOT NULL,
    email VARCHAR(60) NOT NULL,
    ra VARCHAR(13) NOT NULL,
    idvotacao INT,
    FOREIGN KEY (idvotacao) REFERENCES tb_votacoes(idvotacao)
);

ALTER TABLE tb_votacoes
ADD CONSTRAINT fk_rep
    FOREIGN KEY (idcandidato_representante)
    REFERENCES tb_candidatos(idcandidato);

ALTER TABLE tb_votacoes
ADD CONSTRAINT fk_sup
    FOREIGN KEY (idcandidato_suplente)
    REFERENCES tb_candidatos(idcandidato);

CREATE TABLE tb_votos (
    idvoto INT PRIMARY KEY AUTO_INCREMENT,
    datavoto DATETIME NOT NULL,
    idaluno INT NOT NULL,
    idcandidato INT NOT NULL,
    FOREIGN KEY (idaluno) REFERENCES tb_alunos(idaluno),
    FOREIGN KEY (idcandidato) REFERENCES tb_candidatos(idcandidato)
);

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO tb_candidatos (idcandidato, nomealuno, ra, email)
VALUES (0, 'VOTO NULO', '0000000000000', 'nulo@sistema.local');

SET SQL_MODE = '';

INSERT INTO tb_administradores (nome, email, senha)
VALUES
('Alexandre da silva', 'alexandre.silva02@fatec.sp.gov.br', 'alesiva');

INSERT INTO tb_alunos (nome, ra, email, senha, curso, semestre, idvotacao)
VALUES

-- Gestão Empresarial
('Lucas Ferreira', '2780642613001', 'lucas.ferreira@fatec.sp.gov.br', 'Lf8921aa', 'Gestão Empresarial', 1, NULL),
('Mariana Duarte', '2780642613002', 'mariana.duarte@fatec.sp.gov.br', 'Md4472bb', 'Gestão Empresarial', 1, NULL),
('Ricardo Alves', '2780642613003', 'ricardo.alves@fatec.sp.gov.br', 'Ra7315cc', 'Gestão Empresarial', 1, NULL),
('Juliana Campos', '2780642613004', 'juliana.campos@fatec.sp.gov.br', 'Jc1289dd', 'Gestão Empresarial', 1, NULL),
('Gabriel Manrique', '2780642613005', 'gabriel.manrique@fatec.sp.gov.br', '12345678', 'Gestão Empresarial', 1, NULL),
('Ana Ribeiro', '2780642613006', 'ana.ribeiro@fatec.sp.gov.br', 'Ar8744ff', 'Gestão Empresarial', 1, NULL),
('Thiago Moreira', '2780642613007', 'thiago.moreira@fatec.sp.gov.br', 'Tm2248gg', 'Gestão Empresarial', 1, NULL),
('Patricia Gomes', '2780642613008', 'patricia.gomes@fatec.sp.gov.br', 'Pg6012hh', 'Gestão Empresarial', 1, NULL),
('Felipe Cardoso', '2780642613009', 'felipe.cardoso@fatec.sp.gov.br', 'Fc9150ii', 'Gestão Empresarial', 1, NULL),
('Beatriz Moura', '2780642613010', 'beatriz.moura@fatec.sp.gov.br', 'Bm3782jj', 'Gestão Empresarial', 1, NULL),

-- Desenvolvimento de Software Multiplataforma
('Carlos Nogueira', '2781392613001', 'carlos.nogueira@fatec.sp.gov.br', 'Cn1287kk', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Luis Porfirio', '2781392613002', 'luis.porfirio@fatec.sp.gov.br', '12345678', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Henrique Costa', '2781392613003', 'henrique.costa@fatec.sp.gov.br', 'Hc6610mm', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Natália Silva', '2781392613004', 'natalia.silva@fatec.sp.gov.br', 'Ns3118nn', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Rafael Rocha', '2781392613005', 'rafael.rocha@fatec.sp.gov.br', 'Rr7094oo', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Isabela Castro', '2781392613006', 'isabela.castro@fatec.sp.gov.br', 'Ic2046pp', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Bruno Teixeira', '2781392613007', 'bruno.teixeira@fatec.sp.gov.br', 'Bt7745qq', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Camila Rezende', '2781392613008', 'camila.rezende@fatec.sp.gov.br', 'Cr5580rr', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Eduardo Lima', '2781392613009', 'eduardo.lima@fatec.sp.gov.br', 'El1439ss', 'Desenvolvimento de Software Multiplataforma', 1, NULL),
('Vanessa Araujo', '2781392613010', 'vanessa.araujo@fatec.sp.gov.br', 'Va8297tt', 'Desenvolvimento de Software Multiplataforma', 1, NULL),

-- Gestão de Produção Industrial
('João Figueiredo', '2781622613001', 'joao.figueiredo@fatec.sp.gov.br', 'Jf5001uu', 'Gestão de Produção Industrial', 1, NULL),
('Larissa Barros', '2781622613002', 'larissa.barros@fatec.sp.gov.br', 'Lb3390vv', 'Gestão de Produção Industrial', 1, NULL),
('Matheus Prado', '2781622613003', 'matheus.prado@fatec.sp.gov.br', 'Mp4682ww', 'Gestão de Produção Industrial', 1, NULL),
('Pedro Fernandes', '2781622613004', 'pedro.fernandes@fatec.sp.gov.br', '12345678', 'Gestão de Produção Industrial', 1, NULL),
('André Pires', '2781622613005', 'andre.pires@fatec.sp.gov.br', 'Ap1109yy', 'Gestão de Produção Industrial', 1, NULL),
('Helena Batista', '2781622613006', 'helena.batista@fatec.sp.gov.br', 'Hb4470zz', 'Gestão de Produção Industrial', 1, NULL),
('Rogério Simões', '2781622613007', 'rogerio.simoes@fatec.sp.gov.br', 'Rs9031aa', 'Gestão de Produção Industrial', 1, NULL),
('Sabrina Lopes', '2781622613008', 'sabrina.lopes@fatec.sp.gov.br', 'Sl2608bb', 'Gestão de Produção Industrial', 1, NULL),
('Diego Barcellos', '2781622613009', 'diego.barcellos@fatec.sp.gov.br', 'Db7764cc', 'Gestão de Produção Industrial', 1, NULL),
('Tatiane Moraes', '2781622613010', 'tatiane.moraes@fatec.sp.gov.br', 'Tm8420dd', 'Gestão de Produção Industrial', 1, NULL);

-- ============================================
-- SCRIPT DE INSERÇÃO PARA APRESENTAÇÃO
-- Data: 01/12/2025
-- ============================================

USE astros;

-- ============================================
-- 1. INSERIR VOTAÇÕES
-- ============================================

-- Votação 1: Desenvolvimento de Software Multiplataforma - 1º Semestre
INSERT INTO tb_votacoes (curso, semestre, ativa, data_inicio, data_candidatura, data_final, idadmin, idcandidato_representante, idcandidato_suplente)
VALUES (
    'Desenvolvimento de Software Multiplataforma',
    1,
    'sim',
    '2025-11-30 00:00:00',
    '2025-11-28 00:00:00',
    '2025-12-01 23:59:59',
    1,
    NULL,
    NULL
);

-- Votação 2: Gestão Empresarial - 1º Semestre
INSERT INTO tb_votacoes (curso, semestre, ativa, data_inicio, data_candidatura, data_final, idadmin, idcandidato_representante, idcandidato_suplente)
VALUES (
    'Gestão Empresarial',
    1,
    'sim',
    '2025-11-29 00:00:00',
    '2025-11-28 00:00:00',
    '2025-12-10 23:59:59',
    1,
    NULL,
    NULL
);

-- ============================================
-- 2. ATUALIZAR ALUNOS COM IDVOTACAO
-- ============================================

-- Vincular alunos de DSM à votação 1 (idvotacao = 1)
UPDATE tb_alunos 
SET idvotacao = 1 
WHERE curso = 'Desenvolvimento de Software Multiplataforma' AND semestre = 1;

-- Vincular alunos de Gestão Empresarial à votação 2 (idvotacao = 2)
UPDATE tb_alunos 
SET idvotacao = 2 
WHERE curso = 'Gestão Empresarial' AND semestre = 1;

-- ============================================
-- 3. INSERIR CANDIDATOS
-- ============================================

-- Candidatos de Gestão Empresarial (idvotacao = 2)
INSERT INTO tb_candidatos (imagem, nomealuno, email, ra, idvotacao)
VALUES 
(NULL, 'Lucas Ferreira', 'lucas.ferreira@fatec.sp.gov.br', '2780642613001', 2),
(NULL, 'Mariana Duarte', 'mariana.duarte@fatec.sp.gov.br', '2780642613002', 2),
(NULL, 'Ricardo Alves', 'ricardo.alves@fatec.sp.gov.br', '2780642613003', 2);

-- Candidatos de DSM (idvotacao = 1)
INSERT INTO tb_candidatos (imagem, nomealuno, email, ra, idvotacao)
VALUES 
(NULL, 'Carlos Nogueira', 'carlos.nogueira@fatec.sp.gov.br', '2781392613001', 1),
(NULL, 'Luis Porfirio', 'luis.porfirio@fatec.sp.gov.br', '2781392613002', 1),
(NULL, 'Henrique Costa', 'henrique.costa@fatec.sp.gov.br', '2781392613003', 1),
(NULL, 'Natália Silva', 'natalia.silva@fatec.sp.gov.br', '2781392613004', 1);

-- ============================================
-- 4. INSERIR VOTOS - GESTÃO EMPRESARIAL
-- ============================================

-- 4 votos para Lucas Ferreira (idcandidato = 1)
-- Eleitores: Juliana Campos, Ana Ribeiro, Thiago Moreira, Patricia Gomes
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-29 10:30:00', 4, 1),  -- Juliana Campos
('2025-11-29 11:15:00', 6, 1),  -- Ana Ribeiro
('2025-11-29 14:20:00', 7, 1),  -- Thiago Moreira
('2025-11-29 16:45:00', 8, 1);  -- Patricia Gomes

-- 3 votos para Mariana Duarte (idcandidato = 2)
-- Eleitores: Felipe Cardoso, Beatriz Moura, Lucas Ferreira
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-29 09:00:00', 9, 2),   -- Felipe Cardoso
('2025-11-29 13:30:00', 10, 2),  -- Beatriz Moura
('2025-11-29 15:00:00', 1, 2);   -- Lucas Ferreira

-- 2 votos para Ricardo Alves (idcandidato = 3)
-- Eleitores: Mariana Duarte, Ricardo Alves
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-29 10:00:00', 2, 3),   -- Mariana Duarte
('2025-11-29 12:00:00', 3, 3);   -- Ricardo Alves

-- ============================================
-- 5. INSERIR VOTOS - DSM
-- ============================================

-- 4 votos para Luis Porfirio (idcandidato = 5)
-- Eleitores: Rafael Rocha, Isabela Castro, Bruno Teixeira, Camila Rezende
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-30 09:15:00', 15, 5),  -- Rafael Rocha
('2025-11-30 10:30:00', 16, 5),  -- Isabela Castro
('2025-11-30 11:45:00', 17, 5),  -- Bruno Teixeira
('2025-11-30 14:00:00', 18, 5);  -- Camila Rezende

-- 3 votos para Carlos Nogueira (idcandidato = 4)
-- Eleitores: Eduardo Lima, Vanessa Araujo, Henrique Costa
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-30 09:00:00', 19, 4),  -- Eduardo Lima
('2025-11-30 10:00:00', 20, 4),  -- Vanessa Araujo
('2025-11-30 13:00:00', 13, 4);  -- Henrique Costa

-- 1 voto para Henrique Costa (idcandidato = 6)
-- Eleitor: Carlos Nogueira
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-30 11:00:00', 11, 6);  -- Carlos Nogueira

-- 1 voto NULO (idcandidato = 0)
-- Eleitor: Natália Silva
INSERT INTO tb_votos (datavoto, idaluno, idcandidato)
VALUES 
('2025-11-30 15:30:00', 14, 0);  -- Natália Silva (voto nulo)

-- ============================================
-- RESUMO DOS ALUNOS QUE NÃO VOTARAM
-- ============================================
-- Luis Porfirio (idaluno = 12) - DSM - Não votou
-- Gabriel Manrique (idaluno = 5) - Gestão Empresarial - Não votou

-- ============================================
-- VERIFICAÇÕES (OPCIONAL - PARA CONFERIR)
-- ============================================

-- Total de votos por candidato em Gestão Empresarial
-- SELECT c.nomealuno, COUNT(v.idvoto) as total_votos
-- FROM tb_candidatos c
-- LEFT JOIN tb_votos v ON c.idcandidato = v.idcandidato
-- WHERE c.idvotacao = 2
-- GROUP BY c.idcandidato, c.nomealuno
-- ORDER BY total_votos DESC;

-- Total de votos por candidato em DSM
-- SELECT c.nomealuno, COUNT(v.idvoto) as total_votos
-- FROM tb_candidatos c
-- LEFT JOIN tb_votos v ON c.idcandidato = v.idcandidato
-- WHERE c.idvotacao = 1
-- GROUP BY c.idcandidato, c.nomealuno
-- ORDER BY total_votos DESC;

-- Verificar votos nulos
-- SELECT COUNT(*) as votos_nulos FROM tb_votos WHERE idcandidato = 0;

-- Alunos que não votaram em DSM
-- SELECT a.nome, a.ra 
-- FROM tb_alunos a
-- WHERE a.idvotacao = 1 
-- AND a.idaluno NOT IN (SELECT idaluno FROM tb_votos WHERE idcandidato IN (4,5,6,7,0))
-- ORDER BY a.nome;

-- Alunos que não votaram em Gestão Empresarial
-- SELECT a.nome, a.ra 
-- FROM tb_alunos a
-- WHERE a.idvotacao = 2 
-- AND a.idaluno NOT IN (SELECT idaluno FROM tb_votos WHERE idcandidato IN (1,2,3))
-- ORDER BY a.nome;