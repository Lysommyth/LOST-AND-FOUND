--tables.sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NULL, -- Nullable for now since we are testing verification
    verification_token VARCHAR(255) NULL,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE INDEX email (email)
) ENGINE=InnoDB;

CREATE TABLE claims (
    claim_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_id INT NOT NULL, -- The person claiming the item
    claim_details TEXT NOT NULL, -- e.g., "My laptop has a Strathmore sticker on the back"
    claim_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    claimed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    finder_id INT NOT NULL, -- Links to the 'id' in your users table
    item_name VARCHAR(150) NOT NULL,
    category ENUM('Electronics', 'Books', 'Clothing', 'Keys', 'IDs/Wallets', 'Other') NOT NULL,
    description TEXT NOT NULL,
    location_found VARCHAR(255) NOT NULL, -- e.g., "STC Cafeteria" or "Library Phase 2"
    status ENUM('available', 'claimed', 'returned') DEFAULT 'available',
    image_path VARCHAR(255), -- Stores the file path of the uploaded photo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (finder_id) REFERENCES users(id) ON DELETE CASCADE
);