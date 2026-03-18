-- ============================================================
-- Base de données : elci_cours_soir
-- Description     : Gestion des cours du soir à ELCI
-- Date            : 2025-10-21
-- ============================================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS elci_cours_soir;
USE elci_cours_soir;

-- Table des étudiants
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    birth_date DATE NOT NULL,
    birth_place VARCHAR(255) NOT NULL,
    student_phone VARCHAR(20) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des notes (avec gestion des inscriptions actives)
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    language VARCHAR(50) NOT NULL,
    level VARCHAR(50) NOT NULL,
    t1 DECIMAL(5,3) NOT NULL DEFAULT 0.000,
    t2 DECIMAL(5,3) NOT NULL DEFAULT 0.000,
    average DECIMAL(5,3) NOT NULL DEFAULT 0.000,
    status VARCHAR(20) NOT NULL DEFAULT 'En cours',
    exam_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table du bloc-notes administratif
CREATE TABLE IF NOT EXISTS admin_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Données de test (optionnel - supprimez si non nécessaire)
-- ============================================================



-- ============================================================
-- Nettoyage des doublons (à exécuter une fois si nécessaire)
-- ============================================================
-- DELETE g1 FROM grades g1
-- INNER JOIN grades g2 
-- WHERE g1.id < g2.id 
--   AND g1.student_id = g2.student_id 
--   AND g1.language = g2.language 
--   AND g1.level = g2.level 
--   AND g1.is_active = 1 
--   AND g2.is_active = 1;
ALTER TABLE admin_notes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Dans les requêtes SQL
SELECT 
    language,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Admis' THEN 1 ELSE 0 END) as admis,
    SUM(CASE WHEN status = 'Échec' THEN 1 ELSE 0 END) as echec
FROM grades
WHERE status != 'En cours'  -- ← Ajoutez cette ligne
GROUP BY language