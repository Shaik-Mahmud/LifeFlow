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

INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin2', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin3', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin4', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin5', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin6', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin7', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin8', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin9', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin10', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin11', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin12', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin13', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin14', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin15', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin16', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin17', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin18', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin19', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('admin20', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2');

INSERT INTO donors (full_name, phone, email, dob, blood_group, weight, city, area, password) VALUES 
('John Doe', '01711223344', 'john@example.com', '1995-05-15', 'A+', 75, 'Dhaka', 'Mirpur', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Jane Smith', '01811223344', 'jane@example.com', '1992-08-20', 'O-', 60, 'Dhaka', 'Gulshan', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Ali Khan', '01911223344', 'ali@example.com', '1988-11-10', 'B+', 82, 'Dhaka', 'Banani', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Sara Rahman', '01611223344', 'sara@example.com', '1998-02-25', 'AB+', 55, 'Dhaka', 'Uttara', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Kamal Hossain', '01511223344', 'kamal@example.com', '1990-07-30', 'O+', 70, 'Dhaka', 'Dhanmondi', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Rina Akter', '01722334455', 'rina@example.com', '1996-09-12', 'A-', 65, 'Dhaka', 'Mohammadpur', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Tarikul Islam', '01822334455', 'tarik@example.com', '1985-04-05', 'B-', 78, 'Dhaka', 'Badda', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Nadia Sultana', '01922334455', 'nadia@example.com', '1993-12-18', 'AB-', 58, 'Dhaka', 'Khilgaon', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Faisal Ahmed', '01622334455', 'faisal@example.com', '1991-06-22', 'O+', 85, 'Dhaka', 'Malibagh', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Mita Chowdhury', '01522334455', 'mita@example.com', '1997-03-08', 'A+', 62, 'Dhaka', 'Rampura', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Rakib Hasan', '01733445566', 'rakib@example.com', '1994-10-15', 'B+', 72, 'Dhaka', 'Motijheel', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Sumaiya Parvin', '01833445566', 'sumaiya@example.com', '1999-01-28', 'O-', 54, 'Dhaka', 'Farmgate', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Zahidul Islam', '01933445566', 'zahid@example.com', '1987-08-03', 'AB+', 76, 'Dhaka', 'Tejgaon', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Ayesha Siddiqa', '01633445566', 'ayesha@example.com', '1995-11-20', 'A-', 60, 'Dhaka', 'Banani', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Imran Hossain', '01533445566', 'imran@example.com', '1992-05-09', 'O+', 80, 'Dhaka', 'Gulshan', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Tania Akter', '01744556677', 'tania@example.com', '1998-07-14', 'B-', 56, 'Dhaka', 'Mirpur', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Shafiqul Islam', '01844556677', 'shafiq@example.com', '1989-02-27', 'AB-', 74, 'Dhaka', 'Uttara', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Nusrat Jahan', '01944556677', 'nusrat@example.com', '1996-10-05', 'A+', 63, 'Dhaka', 'Dhanmondi', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Arif Rahman', '01644556677', 'arif@example.com', '1993-04-19', 'O-', 81, 'Dhaka', 'Mohammadpur', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2'),
('Lima Begum', '01544556677', 'lima@example.com', '1997-12-30', 'B+', 59, 'Dhaka', 'Badda', '$2y$10$swHGaHj/TNTjZJlVHfyW1.XyP4.qmRJGn0l4bSJjhtKviPS0g3BN2');

INSERT INTO blood_requests (patient_name, blood_group, units, urgency, hospital, city, address, contact_name, phone, status) VALUES 
('Patient A', 'O+', 2, 'Urgent', 'Dhaka Medical College', 'Dhaka', 'Secretariat Road', 'Contact A', '01700112233', 'Pending'),
('Patient B', 'A-', 1, 'Scheduled', 'Square Hospital', 'Dhaka', 'Panthapath', 'Contact B', '01800112233', 'Completed'),
('Patient C', 'B+', 3, 'Urgent', 'Apollo Hospital', 'Dhaka', 'Bashundhara', 'Contact C', '01900112233', 'Matching donors'),
('Patient D', 'AB+', 2, 'Scheduled', 'United Hospital', 'Dhaka', 'Gulshan', 'Contact D', '01600112233', 'Pending'),
('Patient E', 'O-', 1, 'Urgent', 'BIRDEM', 'Dhaka', 'Shahbag', 'Contact E', '01500112233', 'Completed'),
('Patient F', 'A+', 4, 'Scheduled', 'Labaid Hospital', 'Dhaka', 'Dhanmondi', 'Contact F', '01700223344', 'Pending'),
('Patient G', 'B-', 2, 'Urgent', 'Kurmitola General', 'Dhaka', 'Cantonment', 'Contact G', '01800223344', 'Matching donors'),
('Patient H', 'AB-', 1, 'Scheduled', 'Ibn Sina Hospital', 'Dhaka', 'Kalyanpur', 'Contact H', '01900223344', 'Pending'),
('Patient I', 'O+', 3, 'Urgent', 'Holy Family Hospital', 'Dhaka', 'Eskaton', 'Contact I', '01600223344', 'Completed'),
('Patient J', 'A-', 2, 'Scheduled', 'Popular Hospital', 'Dhaka', 'Shantinagar', 'Contact J', '01500223344', 'Pending'),
('Patient K', 'B+', 1, 'Urgent', 'Sir Salimullah Med', 'Dhaka', 'Mitford Road', 'Contact K', '01700334455', 'Matching donors'),
('Patient L', 'AB+', 2, 'Scheduled', 'Enam Medical', 'Dhaka', 'Savar', 'Contact L', '01800334455', 'Pending'),
('Patient M', 'O-', 3, 'Urgent', 'Shaheed Suhrawardy', 'Dhaka', 'Sher-e-Bangla Nagar', 'Contact M', '01900334455', 'Completed'),
('Patient N', 'A+', 1, 'Scheduled', 'Green Life Hospital', 'Dhaka', 'Green Road', 'Contact N', '01600334455', 'Pending'),
('Patient O', 'B-', 2, 'Urgent', 'Central Hospital', 'Dhaka', 'Dhanmondi', 'Contact O', '01500334455', 'Matching donors'),
('Patient P', 'AB-', 4, 'Scheduled', 'BRB Hospital', 'Dhaka', 'Panthapath', 'Contact P', '01700445566', 'Pending'),
('Patient Q', 'O+', 1, 'Urgent', 'Mugda Medical', 'Dhaka', 'Mugda', 'Contact Q', '01800445566', 'Completed'),
('Patient R', 'A-', 2, 'Scheduled', 'Comfort Hospital', 'Dhaka', 'Green Road', 'Contact R', '01900445566', 'Pending'),
('Patient S', 'B+', 3, 'Urgent', 'Faridabad Hospital', 'Dhaka', 'Faridabad', 'Contact S', '01600445566', 'Matching donors'),
('Patient T', 'AB+', 1, 'Scheduled', 'Bangladesh Eye Hosp', 'Dhaka', 'Dhanmondi', 'Contact T', '01500445566', 'Pending');

INSERT INTO messages (name, email, subject, message) VALUES 
('User 1', 'user1@example.com', 'Subject 1', 'Message 1'),
('User 2', 'user2@example.com', 'Subject 2', 'Message 2'),
('User 3', 'user3@example.com', 'Subject 3', 'Message 3'),
('User 4', 'user4@example.com', 'Subject 4', 'Message 4'),
('User 5', 'user5@example.com', 'Subject 5', 'Message 5'),
('User 6', 'user6@example.com', 'Subject 6', 'Message 6'),
('User 7', 'user7@example.com', 'Subject 7', 'Message 7'),
('User 8', 'user8@example.com', 'Subject 8', 'Message 8'),
('User 9', 'user9@example.com', 'Subject 9', 'Message 9'),
('User 10', 'user10@example.com', 'Subject 10', 'Message 10'),
('User 11', 'user11@example.com', 'Subject 11', 'Message 11'),
('User 12', 'user12@example.com', 'Subject 12', 'Message 12'),
('User 13', 'user13@example.com', 'Subject 13', 'Message 13'),
('User 14', 'user14@example.com', 'Subject 14', 'Message 14'),
('User 15', 'user15@example.com', 'Subject 15', 'Message 15'),
('User 16', 'user16@example.com', 'Subject 16', 'Message 16'),
('User 17', 'user17@example.com', 'Subject 17', 'Message 17'),
('User 18', 'user18@example.com', 'Subject 18', 'Message 18'),
('User 19', 'user19@example.com', 'Subject 19', 'Message 19'),
('User 20', 'user20@example.com', 'Subject 20', 'Message 20');

INSERT INTO donations (donor_id, request_id, donation_date, units_donated, status) VALUES 
(1, 1, '2023-10-01', 1, 'Completed'),
(2, 2, '2023-10-02', 1, 'Completed'),
(3, 3, '2023-10-03', 1, 'Completed'),
(4, 4, '2023-10-04', 1, 'Completed'),
(5, 5, '2023-10-05', 1, 'Completed'),
(6, 6, '2023-10-06', 1, 'Completed'),
(7, 7, '2023-10-07', 1, 'Completed'),
(8, 8, '2023-10-08', 1, 'Completed'),
(9, 9, '2023-10-09', 1, 'Completed'),
(10, 10, '2023-10-10', 1, 'Completed'),
(11, 11, '2023-10-11', 1, 'Completed'),
(12, 12, '2023-10-12', 1, 'Completed'),
(13, 13, '2023-10-13', 1, 'Completed'),
(14, 14, '2023-10-14', 1, 'Completed'),
(15, 15, '2023-10-15', 1, 'Completed'),
(16, 16, '2023-10-16', 1, 'Completed'),
(17, 17, '2023-10-17', 1, 'Completed'),
(18, 18, '2023-10-18', 1, 'Completed'),
(19, 19, '2023-10-19', 1, 'Completed'),
(20, 20, '2023-10-20', 1, 'Completed');

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
