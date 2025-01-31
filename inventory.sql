CREATE TABLE inventory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR(255),
  dc_drc_lb VARCHAR(10), 
  environment VARCHAR(20),
  url VARCHAR(255),
  ip_address VARCHAR(50) UNIQUE,
  protocol VARCHAR(10),
  port SMALLINT,
  username VARCHAR(50),
  password VARCHAR(50),
  new_ip VARCHAR(50),
  new_port SMALLINT,
  remarks TEXT,
  serial_number VARCHAR(50) UNIQUE
);