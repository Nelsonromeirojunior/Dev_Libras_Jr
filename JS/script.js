(function() {
    // Smooth scroll to sections
    function scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            const offsetTop = element.offsetTop - 100;
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
        updateActiveNavLink(sectionId);
    }

    // Update active navigation link
    function updateActiveNavLink(activeSection) {
        const navLinks = document.querySelectorAll('.nav-link-custom');
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${activeSection}`) {
                link.classList.add('active');
            }
        });
    }

    // Track scroll position for active navigation
    function handleScroll() {
        const sections = ['home', 'about', 'skills', 'social'];
        let currentSection = 'home';
        sections.forEach(sectionId => {
            const element = document.getElementById(sectionId);
            if (element) {
                const rect = element.getBoundingClientRect();
                if (rect.top <= 150 && rect.bottom >= 150) {
                    currentSection = sectionId;
                }
            }
        });
        updateActiveNavLink(currentSection);
    }

    // Initialize event listeners
    window.addEventListener('scroll', handleScroll);

    // Make scrollToSection globally available
    window.scrollToSection = scrollToSection;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set current year
        document.getElementById("current-year").textContent = new Date().getFullYear();

        handleScroll();

        // VLibras fallback with better error handling
        setTimeout(() => {
            if (!window.VLibras) {
                const vlibrasNotice = document.querySelector('.vlibras-notice');
                if (vlibrasNotice) {
                    vlibrasNotice.innerHTML = '⚠️ Plugin VLibras não carregado. Tente recarregar a página.';
                    vlibrasNotice.style.background = '#ef4444';
                }
            }
        }, 3000);

        // Handle navbar collapse on mobile
        document.querySelectorAll('.nav-link-custom').forEach(link => {
            link.addEventListener('click', () => {
                const navbarCollapse = document.getElementById('navbarNav');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });

        // Enhanced animation observers
        const cards = document.querySelectorAll('.timeline-card, .skill-card, .social-card, .hero-card');
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Enhanced hover effects for skill cards
        document.querySelectorAll('.skill-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.background = 'rgba(255, 255, 255, 1)';
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.background = 'rgba(255, 255, 255, 0.95)';
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add click effects to buttons and social links
        document.querySelectorAll('.btn-custom-primary, .btn-custom-secondary, .social-link').forEach(btn => {
            btn.addEventListener('click', function() {
                this.style.transform = 'translateY(-3px) scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-3px) scale(1)';
                }, 150);
            });
        });

        // Smooth reveal for timeline items with stagger
        const timelineItems = document.querySelectorAll('.timeline-item');
        timelineItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-50px)';
            item.style.transition = `all 0.6s ease ${index * 0.2}s`;
        });

        // Trigger timeline animation on scroll
        const timelineObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateX(0)';
                }
            });
        }, { threshold: 0.2 });

        timelineItems.forEach(item => {
            timelineObserver.observe(item);
        });

        // Add accessibility improvements for keyboard navigation
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                scrollToSection(targetId);
            });
        });

        console.log('DEV Libras Júnior - Portfolio carregado com sucesso! 🤟🏻💻');
    });

    // VLibras initialization with error handling
    try {
        if (window.VLibras) {
            new window.VLibras.Widget("https://vlibras.gov.br/app");
        }
    } catch (error) {
        console.warn('VLibras não pôde ser inicializado:', error);
    }

})()