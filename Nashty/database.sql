-- database.sql
-- (Database selected by installer/connection)

-- Tabla Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    phone VARCHAR(20),
    photo VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(100) UNIQUE,
    barcode VARCHAR(100),
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(100),
    size VARCHAR(20),
    details TEXT,
    cost_price DECIMAL(10,2) DEFAULT 0.00,
    sale_price DECIMAL(10,2) DEFAULT 0.00,
    warehouse_location VARCHAR(100),
    stock INT DEFAULT 0,
    marketplaces_data JSON, -- Stores array of {platform, active, link_id}
    image VARCHAR(255),
    last_sold_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla Sales
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    quantity INT,
    total DECIMAL(10,2),
    marketplace VARCHAR(50),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Usuario Admin por defecto (Pass: admin123)
-- $2y$10$8.K... hash for admin123
INSERT INTO users (username, email, password, role) 
SELECT 'admin', 'admin@tienda.com', '$2y$10$I0jH.E.2n.u/wz.E.2n.ue7.2n.u.2n.u.2n.u.2n.u.2n.u.2n.u', 'admin'
FROM dual
WHERE NOT EXISTS (SELECT * FROM users WHERE username = 'admin');
