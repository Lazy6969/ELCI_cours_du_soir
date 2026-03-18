// Mode sombre / clair
function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
}

// Appliquer le thème sauvegardé
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('theme');
    if (saved === 'light') {
        document.body.classList.remove('dark-theme');
    }
});

// Modal déconnexion
function confirmLogout() {
    document.getElementById('logout-modal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('logout-modal').style.display = 'none';
}

// Recherche dynamique (optionnel)
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
    });
}
// ===== GESTION DE LA SUPPRESSION (fonctionne sur toutes les pages) =====
let studentIdToDelete = null;

function showDeleteConfirm(studentId) {
    studentIdToDelete = studentId;
    document.getElementById('delete-modal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
    studentIdToDelete = null;
}

// Met à jour le lien de suppression quand on clique
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'delete-confirm-link') {
        e.preventDefault();
        if (studentIdToDelete) {
            // Redirige vers la suppression
            window.location.href = 'students.php?delete=' + studentIdToDelete;
        }
        closeDeleteModal();
    }
});

// ===== FILTRER LE TABLEAU DES NOTES =====
function filterGradesTable() {
    const input = document.getElementById('searchGrades');
    if (!input) return;

    const filter = input.value.toLowerCase();
    const table = document.querySelector('table.data-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function printFilteredResults() {
    // Sauvegarder les styles originaux
    const originalBodyClass = document.body.className;
    const originalDisplay = [];

    // Cacher tous les éléments non imprimables
    const noPrintElements = document.querySelectorAll('.no-print, .navbar, .theme-toggle, .search-bar, .form-grade, .action-buttons, footer, .btn-print');
    noPrintElements.forEach(el => {
        originalDisplay.push({ el: el, display: window.getComputedStyle(el).display });
        el.style.display = 'none';
    });

    // Ajouter une classe pour forcer le style d'impression
    document.body.classList.add('print-mode');

    // Lancer l'impression
    window.print();

    // Restaurer tout après impression
    setTimeout(() => {
        document.body.className = originalBodyClass;
        noPrintElements.forEach((el, i) => {
            el.style.display = originalDisplay[i].display;
        });
    }, 500);
}

function preparePrint() {
    const rows = document.querySelectorAll('table.data-table tbody tr');
    const results = [];

    rows.forEach(row => {
        // Ne prendre que les lignes visibles
        if (row.style.display === 'none') return;

        const fullname = row.querySelector('.print-fullname')?.textContent.trim();
        const language = row.querySelector('.print-language')?.textContent.trim();
        const level = row.querySelector('.print-level')?.textContent.trim();
        const status = row.querySelector('.print-status')?.textContent.trim();

        if (fullname && language && level && status) {
            results.push({ fullname, language, level, status });
        }
    });

    if (results.length === 0) {
        alert("Aucun résultat visible à imprimer.");
        return;
    }

    // Envoyer vers print_search.php
    const form = document.getElementById('printForm');
    const input = document.getElementById('resultsJsonInput');
    input.value = JSON.stringify(results);
    form.submit();
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('theme');
    if (saved === 'light') {
        document.body.classList.remove('dark-theme');
        document.querySelector('.theme-toggle').textContent = '☀️';
    } else {
        document.body.classList.add('dark-theme');
        document.querySelector('.theme-toggle').textContent = '🌙';
    }
});

function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    const isDark = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Changer l'icône
    const toggleBtn = document.querySelector('.theme-toggle');
    if (toggleBtn) {
        toggleBtn.textContent = isDark ? '🌙' : '☀️';
    }
}

// Après toggleTheme(), rechargez les graphiques (optionnel mais avancé)
// Pour simplicité, on peut juste recharger la page :
function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    const isDark = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    document.querySelector('.theme-toggle').textContent = isDark ? '🌙' : '☀️';
    
    // Option : recharger la page pour les graphiques
    if (window.location.pathname.includes('stats.php')) {
        location.reload();
    }
}

// Animation des cartes au scroll
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stat-card, .quick-link');
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        observer.observe(card);
    });
});

// Effet glow au survol
document.addEventListener('mousemove', function(e) {
    const x = e.clientX;
    const y = e.clientY;
    document.body.style.backgroundPosition = `${x / 10}px ${y / 10}px`;
});
// Défilement des drapeaux
let scrollOffset = 0;

document.addEventListener('scroll', () => {
    scrollOffset = window.scrollY * 0.1;
    document.querySelectorAll('.flag').forEach(flag => {
        flag.style.transform = `translateX(${scrollOffset}px) ${flag.style.transform}`;
    });
});

// Lumière qui suit la souris sur les drapeaux
document.addEventListener('mousemove', function(e) {
    const x = e.clientX;
    const y = e.clientY;
    
    document.querySelectorAll('.flag').forEach(flag => {
        const rect = flag.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        const distance = Math.sqrt((x - centerX) ** 2 + (y - centerY) ** 2);
        const intensity = Math.max(0, 1 - distance / 300); // 300px max
        
        flag.style.filter = `saturate(${150 + intensity * 50}%) brightness(${120 + intensity * 30}%)`;
        flag.style.opacity = 0.25 + intensity * 0.3;
    });
});


document.querySelectorAll('.grade-input').forEach(input => {
    input.addEventListener('change', function() {
        let value = parseFloat(this.value);
        if (value < 0) this.value = 0;
        if (value > 100) this.value = 100;
    });
});

function printFilteredStudents() {
    const rows = document.querySelectorAll('table.data-table tbody tr');
    const students = [];

    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            // On prend les 5 premières colonnes (sans les boutons)
            if (cells.length >= 5) {
                students.push({
                    fullname: cells[0].textContent.trim(),
                    birth_date: cells[1].textContent.trim(),
                    birth_place: cells[2].textContent.trim(),
                    student_phone: cells[3].textContent.trim(),
                    parent_phone: cells[4].textContent.trim()
                });
            }
        }
    });

    if (students.length === 0) {
        alert("Aucun étudiant à imprimer.");
        return;
    }

    document.getElementById('filteredStudentsInput').value = JSON.stringify(students);
    document.getElementById('printStudentForm').submit();
}

// ===== MIGRATION MODAL =====

let migrateGradeId = null;
let migrateLanguage = '';
let migrateLevel = '';

function showMigrateModal(gradeId, language, level) {
    migrateGradeId = gradeId;
    migrateLanguage = language;
    migrateLevel = level;
    
    document.getElementById('migrate-info').textContent = 
        `Langue : ${language} | Niveau actuel : ${level}`;
    
    document.getElementById('migrate-modal').style.display = 'flex';
}
function migrateThisGrade(button) {
    const id = button.getAttribute('data-id');
    const language = button.getAttribute('data-language');
    const level = button.getAttribute('data-level');
    
    showMigrateModal(id, language, level);
}
function closeMigrateModal() {
    document.getElementById('migrate-modal').style.display = 'none';
    migrateGradeId = null;
}

function migrateStudent(direction) {
    if (!migrateGradeId) return;
    
    // Fermer le modal de migration
    closeMigrateModal();
    
    // Appeler migrate_level.php en mode AJAX
    fetch(`migrate_level.php?grade_id=${migrateGradeId}&direction=${direction}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Recharger la page si succès
                window.location.reload();
            } else {
                // Afficher le modal d'erreur
                showErrorModal(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showErrorModal("Une erreur est survenue. Veuillez réessayer.");
        });
}

// === MODAL D'ERREUR ===
function showErrorModal(message) {
    let errorModal = document.getElementById('error-modal');
    if (!errorModal) {
        errorModal = document.createElement('div');
        errorModal.id = 'error-modal';
        errorModal.className = 'modal';
        errorModal.innerHTML = `
            <div class="modal-content delete-modal">
                <h3>⚠️ Migration impossible</h3>
                <p id="error-message"></p>
                <div class="modal-buttons">
                    <button class="btn btn-danger" onclick="closeErrorModal()">Fermer</button>
                </div>
            </div>
        `;
        document.body.appendChild(errorModal);
    }
    document.getElementById('error-message').textContent = message;
    errorModal.style.display = 'flex';
}

function closeErrorModal() {
    const modal = document.getElementById('error-modal');
    if (modal) modal.style.display = 'none';
}




function confirmDeleteGrade(gradeId) {
    if (confirm("Supprimer cette inscription ?\n(Cette action est irréversible)")) {
        fetch(`delete_grade.php?grade_id=${gradeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Supprimer la ligne du tableau
                    const row = document.querySelector(`button[onclick*="grade_id=${gradeId}"]`).closest('tr');
                    row.remove();
                    
                    // Message de succès
                    const message = document.createElement('div');
                    message.className = 'alert success';
                    message.textContent = 'Inscription supprimée avec succès !';
                    document.querySelector('.card').prepend(message);
                    
                    // Disparaître après 3s
                    setTimeout(() => message.remove(), 3000);
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(() => {
                alert("Une erreur est survenue.");
            });
    }
}
