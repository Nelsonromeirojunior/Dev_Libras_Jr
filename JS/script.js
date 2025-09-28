(function () {
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
        const sections = ['home', 'about', 'skills', 'contact'];
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

    // Enhanced form handling with better error handling and validation
    document.getElementById('contactForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const formMessage = document.getElementById('formMessage');
        const formData = new FormData(this);

        // Get form values
        const name = formData.get('name').trim();
        const email = formData.get('email').trim();
        const subject = formData.get('subject').trim();
        const message = formData.get('message').trim();

        // Enhanced validation
        const validationResult = validateFormData(name, email, subject, message);
        if (!validationResult.isValid) {
            showFormMessage(validationResult.message, 'error');
            focusFirstErrorField(validationResult.field);
            return;
        }

        // Set loading state
        setFormLoading(submitBtn, true);
        hideFormMessage();

        try {
            const response = await fetch('processar_contato.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Resposta inválida do servidor');
            }

            const data = await response.json();

            if (data.sucesso) {
                showFormMessage('✅ ' + data.mensagem, 'success');
                this.reset();
                resetFormValidation();

                // Scroll to message for better UX
                setTimeout(() => {
                    formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                showFormMessage('❌ ' + data.mensagem, 'error');
            }

        } catch (error) {
            console.error('Erro no formulário:', error);

            let errorMessage = '❌ ';
            if (error.message.includes('Failed to fetch')) {
                errorMessage += 'Erro de conexão. Verifique sua internet e tente novamente.';
            } else if (error.message.includes('HTTP error')) {
                errorMessage += 'Erro do servidor. Tente novamente em alguns minutos.';
            } else {
                errorMessage += 'Ocorreu um erro inesperado. Tente novamente ou envie diretamente para devlibrasjunior@gmail.com';
            }

            showFormMessage(errorMessage, 'error');
        } finally {
            setFormLoading(submitBtn, false);
        }
    });

    // Enhanced form validation
    function validateFormData(name, email, subject, message) {
        // Nome validation
        if (!name) {
            return { isValid: false, message: '❌ Nome é obrigatório.', field: 'name' };
        }
        if (name.length > 100) {
            return { isValid: false, message: '❌ Nome deve ter no máximo 100 caracteres.', field: 'name' };
        }

        // Email validation
        if (!email) {
            return { isValid: false, message: '❌ E-mail é obrigatório.', field: 'email' };
        }
        if (!isValidEmail(email)) {
            return { isValid: false, message: '❌ Por favor, insira um e-mail válido.', field: 'email' };
        }
        if (email.length > 255) {
            return { isValid: false, message: '❌ E-mail muito longo.', field: 'email' };
        }

        // Subject validation
        if (subject.length > 200) {
            return { isValid: false, message: '❌ Assunto deve ter no máximo 200 caracteres.', field: 'subject' };
        }

        // Message validation
        if (!message) {
            return { isValid: false, message: '❌ Mensagem é obrigatória.', field: 'message' };
        }
        if (message.length > 2000) {
            return { isValid: false, message: '❌ Mensagem deve ter no máximo 2000 caracteres.', field: 'message' };
        }

        return { isValid: true };
    }

    // Focus first error field
    function focusFirstErrorField(fieldName) {
        if (fieldName) {
            const field = document.getElementById(fieldName);
            if (field) {
                field.focus();
                field.style.borderColor = '#ef4444';
            }
        }
    }

    // Reset form validation styles
    function resetFormValidation() {
        document.querySelectorAll('#contactForm input, #contactForm textarea').forEach(field => {
            field.style.borderColor = '#e5e7eb';
        });
    }

    // Set form loading state
    function setFormLoading(submitBtn, isLoading) {
        if (isLoading) {
            submitBtn.innerHTML = '<div class="loading-spinner"></div>Enviando...';
            submitBtn.disabled = true;

            // Disable all form fields
            document.querySelectorAll('#contactForm input, #contactForm textarea').forEach(field => {
                field.disabled = true;
            });
        } else {
            submitBtn.innerHTML = '📤 Enviar Mensagem';
            submitBtn.disabled = false;

            // Re-enable all form fields
            document.querySelectorAll('#contactForm input, #contactForm textarea').forEach(field => {
                field.disabled = false;
            });
        }
    }

    // Show form message with auto-hide for success
    function showFormMessage(text, type) {
        const formMessage = document.getElementById('formMessage');
        formMessage.className = `alert ${type === 'success' ? 'alert-custom-success' : 'alert-custom-error'} fw-medium text-center`;
        formMessage.textContent = text;
        formMessage.style.display = 'block';

        // Auto-hide success messages after 8 seconds
        if (type === 'success') {
            setTimeout(() => {
                hideFormMessage();
            }, 8000);
        }
    }

    // Hide form message
    function hideFormMessage() {
        const formMessage = document.getElementById('formMessage');
        formMessage.style.display = 'none';
    }

    // Enhanced email validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email) && email.length > 5;
    }

    // Real-time validation for form fields
    function setupRealTimeValidation() {
        const nameField = document.getElementById('name');
        const emailField = document.getElementById('email');
        const subjectField = document.getElementById('subject');
        const messageField = document.getElementById('message');

        // Name validation
        nameField.addEventListener('blur', function () {
            if (this.value.trim()) {
                this.style.borderColor = '#10b981';
            } else {
                this.style.borderColor = '#ef4444';
            }
        });

        nameField.addEventListener('input', function () {
            const remaining = 100 - this.value.length;
            if (remaining < 10) {
                this.style.borderColor = remaining < 0 ? '#ef4444' : '#f59e0b';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });

        // Email validation
        emailField.addEventListener('blur', function () {
            if (isValidEmail(this.value)) {
                this.style.borderColor = '#10b981';
            } else if (this.value.trim()) {
                this.style.borderColor = '#ef4444';
            }
        });

        // Subject validation
        subjectField.addEventListener('input', function () {
            const remaining = 200 - this.value.length;
            if (remaining < 20) {
                this.style.borderColor = remaining < 0 ? '#ef4444' : '#f59e0b';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });

        // Message validation
        messageField.addEventListener('blur', function () {
            if (this.value.trim()) {
                this.style.borderColor = '#10b981';
            } else {
                this.style.borderColor = '#ef4444';
            }
        });

        messageField.addEventListener('input', function () {
            const remaining = 2000 - this.value.length;
            if (remaining < 100) {
                this.style.borderColor = remaining < 0 ? '#ef4444' : '#f59e0b';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });

        // Reset border color on focus
        document.querySelectorAll('#contactForm input, #contactForm textarea').forEach(field => {
            field.addEventListener('focus', function () {
                if (this.style.borderColor !== '#10b981') {
                    this.style.borderColor = '#fbbf24';
                }
            });
        });
    }

    // Initialize event listeners
    window.addEventListener('scroll', handleScroll);

    // Make scrollToSection globally available
    window.scrollToSection = scrollToSection;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
        handleScroll();
        setupRealTimeValidation();

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
        const cards = document.querySelectorAll('.timeline-card, .skill-card, .contact-card, .hero-card');
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function (entries) {
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
            card.addEventListener('mouseenter', function () {
                this.style.background = 'rgba(255, 255, 255, 1)';
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function () {
                this.style.background = 'rgba(255, 255, 255, 0.95)';
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add click effects to buttons with better feedback
        document.querySelectorAll('.btn-custom-primary, .btn-custom-secondary').forEach(btn => {
            btn.addEventListener('click', function () {
                if (!this.disabled) {
                    this.style.transform = 'translateY(-3px) scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-3px) scale(1)';
                    }, 150);
                }
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
        const timelineObserver = new IntersectionObserver(function (entries) {
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

        // Add accessibility improvements
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function (e) {
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

})();