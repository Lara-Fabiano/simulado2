-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';


-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `industria_db` DEFAULT CHARACTER SET utf8 ;
USE `industria_db` ;

-- -----------------------------------------------------
-- Table `industria_db`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `industria_db`.`usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `industria_db`.`produto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `industria_db`.`produto` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(100) NOT NULL,
  `nome` VARCHAR(45) NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `estoque_atual` INT NOT NULL DEFAULT 0,
  `estoque_minimo` INT NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `categoria` VARCHAR(100) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `industria_db`.`movimentacao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `industria_db`.`movimentacao` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tipo` INT NOT NULL COMMENT '1-entrada 2-saida',
  `data` DATE NOT NULL,
  `quantidade` INT NOT NULL,
  `saldo_anterior` INT NOT NULL,
  `produto_id` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_movimentacao_produto_idx` (`produto_id` ASC) VISIBLE,
  INDEX `fk_movimentacao_usuario1_idx` (`usuario_id` ASC) VISIBLE,
  CONSTRAINT `fk_movimentacao_produto`
    FOREIGN KEY (`produto_id`)
    REFERENCES `industria_db`.`produto` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movimentacao_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `industria_db`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


INSERT INTO usuario (id,nome,email,senha) VALUES (1,'João','joao@email.com','123');
INSERT INTO usuario (id,nome,email,senha) VALUES (2,'Lara','lara@email.com','123');
INSERT INTO usuario (id,nome,email,senha) VALUES (3,'Kauany','kau@email.com','123');

INSERT INTO produto (id, codigo, nome, preco, estoque_atual,
						estoque_minimo, ativo, categoria)
			VALUE(1, 'P001', 'Parafuso', 2.50, 100, 50, 1, 'Peça'),
				(2, 'P002', 'Barra Metálica', 50.00, 100, 30, 1, 'Barras'), 
				(3, 'P003', 'Chapa de Aço', 10.00, 100, 20, 1, 'Chapas');
            
INSERT INTO movimentacao (tipo, data, quantidade, saldo_anterior, usuario_id, produto_id)
			VALUES(1, str_to_date('11/09/2026', '%d/%m/%Y'), 5, 2, 1, 3),
				(2, str_to_date('11/09/2026', '%d/%m/%Y'), 10, 20, 2, 2),
				(3, str_to_date('10/09/2026', '%d/%m/%Y'), 15, 50, 3, 1);
				
                

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
