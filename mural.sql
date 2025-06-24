-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 27/04/2025 às 20:04
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS mural;
CREATE DATABASE mural;
USE mural;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mural`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `conteudo` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `mensagens`
--

INSERT INTO `mensagens` (`id`, `titulo`, `conteudo`, `criado_em`, `user_id`, `tag_id`) VALUES
(1, 'Cachorro perdido na região central', 'Perdi meu cachorro ontem à noite na região central da cidade. Ele atende por Thor, é um labrador preto e estava com coleira azul.', '2025-05-25 18:43:00', 1, 2),
(2, 'Carteira encontrada no parque', 'Encontrei uma carteira marrom no parque municipal hoje de manhã. Contém documentos no nome de Ricardo Silva. Estou à disposição para devolver.', '2025-05-24 09:30:00', 2, 3),
(3, 'Ajuda com doação de roupas', 'Olá! Estou organizando uma campanha de doação de roupas para moradores de rua. Quem puder contribuir com agasalhos será muito bem-vindo!', '2025-05-22 14:18:00', 3, 3),
(4, 'Procura-se gato desaparecido', 'Nosso gato, chamado Mingau, desapareceu no bairro Jardim das Flores há três dias. Ele é branco com manchas cinzas. Por favor, entre em contato se avistá-lo.', '2025-05-21 10:45:00', 1, 4),
(5, 'Preciso de carona para hospital', 'Estou precisando de uma carona urgente até o Hospital Municipal. Alguém disponível nas proximidades?', '2025-05-20 08:15:00', 1, 2),
(6, 'Chaves perdidas na faculdade', 'Perdi um chaveiro com três chaves e um chaveiro do Batman hoje no campus. Se alguém encontrar, por favor me avise.', '2025-05-19 11:55:00', 2, 2),
(7, 'Ajuda para encontrar documento perdido', 'Perdi meu RG provavelmente entre o terminal de ônibus e o supermercado. Em nome de Ana Beatriz. Gratifico quem encontrar.', '2025-05-18 16:10:00', 2, 2),
(8, 'Ofereço ajuda com transporte', 'Tenho um carro e estou disponível para ajudar quem precisar de transporte para hospitais ou compras urgentes.', '2025-05-17 20:00:00', 3, 1),
(9, 'Roubo de bicicleta', 'Minha bicicleta foi roubada ontem à noite perto do mercado do bairro. É azul com adesivos pretos. Se alguém viu algo, por favor me avise.', '2025-05-15 22:40:00', 3, 2),
(10, 'Doações para abrigo animal', 'Estamos arrecadando ração e cobertores para o abrigo municipal de animais. Toda ajuda é bem-vinda.', '2025-05-14 09:00:00', 1, 1);

INSERT INTO `mensagens` (`id`, `titulo`, `conteudo`, `criado_em`, `image_path`, `user_id`, `tag_id`) VALUES
(11, 'Gato muito fofo desaparecido 😿', 
'Meu gato chamado Bolinha sumiu ontem à noite (19/05) na região do bairro Bela Vista. Ele é branco com manchas cinza, muito dócil e adora se esfregar nas pernas das pessoas. Por favor, me avisem se virem! Estou desesperada. 💔', 
'2025-05-20 08:22:00', 'uploads/gato-perdido.jpg', 3, 2),
(12, 'Bolinha voltou pra casa! Obrigado a todos 🐾', 
'Quero agradecer de coração a todos que ajudaram a procurar meu gatinho Bolinha. Ele apareceu hoje de manhã na porta de casa, todo sujinho, mas bem! Vocês foram incríveis com as mensagens de apoio e compartilhamentos. 💖', 
'2025-05-24 07:48:00', 'uploads/gato-encontrado.jpeg', 3, 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices de tabela `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Despejando dados para a tabela `tags`
--

INSERT INTO `tags` (`id`, `nome`) VALUES
(4, 'Atualização'),
(3, 'Aviso'),
(1, 'Informativo'),
(2, 'Procura-se');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `telefone` VARCHAR(20),
  `area_atuacao` VARCHAR(255),
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);



CREATE TABLE usuario_interesses (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`),
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'matheus', '$2y$10$gIP2Bmul3sEF8XMbPVj9zuUImvyriWFKC6u2hIM/ntvJu1TVOKtLS', 'admin', '2025-04-23 23:16:44'),
(2, 'joao', '$2y$10$GzsxNA4jZVbR8WvlghzoleAXfbsrScg3lSEq2t08x92OHY0bWJf9m', 'user', '2025-04-23 23:48:42'),
(3, 'alex', '$2y$10$UtjswL8GCWk4Qdc/nk75aeMvOQsWNO2kIQE5FPR.oDEu56Z64MaH.', 'admin', '2025-04-23 23:48:42');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_tag_id` (`tag_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `fk_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

ALTER TABLE `mensagens`
ADD COLUMN `admin_status` ENUM('Pendente', 'Apovada', 'Rejeitada') NOT NULL DEFAULT 'Pendente',
ADD COLUMN `status` ENUM('Não enviada', 'Enviada') NOT NULL DEFAULT 'Não enviada';
