<?php
$bgs = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$urgencies = ['Urgent', 'Scheduled'];
$statuses = ['Pending', 'Completed'];

$sql = <<<EOT
CREATE DATABASE IF NOT EXISTS lifeflow_db;
USE lifeflow_db;

DROP VIEW IF EXISTS vw_blood_requests_summary;
DROP PROCEDURE IF EXISTS sp_process_donation;
DROP TRIGGER IF EXISTS after_donation_insert;

DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS blood_requests;
DROP TABLE IF EXISTS donors;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    weight INT NOT NULL CHECK (weight >= 50),
    last_donation DATE,
    health_notes TEXT,
    city VARCHAR(50) NOT NULL,
    area VARCHAR(50) NOT NULL,
    availability_status VARCHAR(20) DEFAULT 'Available',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (blood_group IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'))
);

CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    units INT NOT NULL CHECK (units > 0),
    urgency VARCHAR(20) NOT NULL CHECK (urgency IN ('Urgent', 'Scheduled')),
    hospital VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    address VARCHAR(255) NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Matching donors', 'Completed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (blood_group IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'))
);

CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    request_id INT NOT NULL,
    donation_date DATE NOT NULL,
    units_donated INT NOT NULL CHECK (units_donated > 0),
    status VARCHAR(20) DEFAULT 'Completed' CHECK (status IN ('Completed', 'Pending', 'Cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'Unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

EOT;

$pwd = '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2';

$sql .= "\nINSERT INTO admins (username, password) VALUES \n";
for ($i = 1; $i <= 20; $i++) {
    $sql .= "('admin{$i}', '{$pwd}')" . ($i == 20 ? ";" : ",") . "\n";
}

$sql .= "\nINSERT INTO donors (full_name, phone, email, dob, blood_group, weight, city, area, password) VALUES \n";
for ($i = 1; $i <= 20; $i++) {
    $bg = $bgs[array_rand($bgs)];
    $num = str_pad($i, 2, '0', STR_PAD_LEFT);
    $sql .= "('Donor {$i}', '017000000{$num}', 'donor{$i}@example.com', '1990-01-01', '{$bg}', 70, 'Dhaka', 'Area {$i}', '{$pwd}')" . ($i == 20 ? ";" : ",") . "\n";
}

$sql .= "\nINSERT INTO blood_requests (patient_name, blood_group, units, urgency, hospital, city, address, contact_name, phone, status) VALUES \n";
for ($i = 1; $i <= 20; $i++) {
    $bg = $bgs[array_rand($bgs)];
    $urg = $urgencies[array_rand($urgencies)];
    $status = $statuses[array_rand($statuses)];
    $num = str_pad($i, 2, '0', STR_PAD_LEFT);
    $sql .= "('Patient {$i}', '{$bg}', 2, '{$urg}', 'Hospital {$i}', 'Dhaka', 'Address {$i}', 'Contact {$i}', '018000000{$num}', '{$status}')" . ($i == 20 ? ";" : ",") . "\n";
}

$sql .= "\nINSERT INTO messages (name, email, subject, message) VALUES \n";
for ($i = 1; $i <= 20; $i++) {
    $sql .= "('User {$i}', 'user{$i}@example.com', 'Subject {$i}', 'Message body {$i}')" . ($i == 20 ? ";" : ",") . "\n";
}

$sql .= "\nINSERT INTO donations (donor_id, request_id, donation_date, units_donated, status) VALUES \n";
for ($i = 1; $i <= 20; $i++) {
    $sql .= "({$i}, {$i}, '2023-10-01', 1, 'Completed')" . ($i == 20 ? ";" : ",") . "\n";
}

$sql .= <<<EOT

-- View: Summarize blood requests
CREATE VIEW vw_blood_requests_summary AS
SELECT 
    br.blood_group,
    COUNT(br.id) as total_requests,
    SUM(CASE WHEN br.status = 'Completed' THEN 1 ELSE 0 END) as completed_requests,
    SUM(br.units) as total_units_requested
FROM blood_requests br
GROUP BY br.blood_group;

-- Procedure: Process a donation safely
DELIMITER //
CREATE PROCEDURE sp_process_donation(IN p_donor_id INT, IN p_request_id INT, IN p_units INT)
BEGIN
    DECLARE v_donor_weight INT;
    
    -- Check constraints using business logic
    SELECT weight INTO v_donor_weight FROM donors WHERE id = p_donor_id;
    
    IF v_donor_weight >= 50 THEN
        START TRANSACTION;
        
        -- Insert donation record
        INSERT INTO donations (donor_id, request_id, donation_date, units_donated, status)
        VALUES (p_donor_id, p_request_id, CURDATE(), p_units, 'Completed');
        
        -- Update request status
        UPDATE blood_requests SET status = 'Completed' WHERE id = p_request_id;
        
        COMMIT;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Donor weight must be at least 50kg';
    END IF;
END //
DELIMITER ;

-- Trigger: Update donor last_donation automatically
DELIMITER //
CREATE TRIGGER after_donation_insert
AFTER INSERT ON donations
FOR EACH ROW
BEGIN
    IF NEW.status = 'Completed' THEN
        UPDATE donors 
        SET last_donation = NEW.donation_date, 
            availability_status = 'Unavailable'
        WHERE id = NEW.donor_id;
    END IF;
END //
DELIMITER ;
EOT;

file_put_contents('database.sql', $sql);
echo "Database SQL generated successfully.";
