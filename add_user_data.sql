ALTER TABLE usuarios
ADD COLUMN telefone VARCHAR(20),
ADD COLUMN area_atuacao VARCHAR(255);

CREATE TABLE usuario_interesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tag_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);
