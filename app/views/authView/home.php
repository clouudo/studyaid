<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyAid - Your AI-Powered Learning Companion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        :root {
            --sa-primary: #6f42c1;
            --sa-primary-dark: #5a32a3;
            --sa-primary-light: #8b5cf6;
            --sa-accent: #e7d5ff;
            --sa-accent-strong: #d4b5ff;
            --sa-gradient: linear-gradient(135deg, #6f42c1 0%, #8b5cf6 50%, #a78bfa 100%);
        }

        * {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(111, 66, 193, 0.1);
            padding: 1rem 0;
        }

        .logo-box img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--sa-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-login {
            background-color: var(--sa-primary);
            border-color: var(--sa-primary);
            color: white;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--sa-primary-dark);
            border-color: var(--sa-primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(111, 66, 193, 0.3);
        }

        .btn-signup {
            background-color: transparent;
            border: 2px solid var(--sa-primary);
            color: var(--sa-primary);
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-signup:hover {
            background-color: var(--sa-accent);
            border-color: var(--sa-primary);
            color: var(--sa-primary);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #f8f4ff 0%, #ede5ff 50%, #e7d5ff 100%);
            min-height: 90vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 150%;
            background: radial-gradient(circle, rgba(111, 66, 193, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-title .highlight {
            background: var(--sa-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: #4a4a68;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            background: var(--sa-gradient);
            border: none;
            color: white;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(111, 66, 193, 0.3);
        }

        .btn-cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(111, 66, 193, 0.4);
            color: white;
        }

        .btn-cta-secondary {
            background: white;
            border: 2px solid var(--sa-primary);
            color: var(--sa-primary);
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-cta-secondary:hover {
            background: var(--sa-accent);
            color: var(--sa-primary);
        }

        .hero-image {
            position: relative;
            z-index: 1;
        }

        .hero-image-container {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 25px 80px rgba(111, 66, 193, 0.15);
            transform: perspective(1000px) rotateY(-5deg);
            transition: transform 0.5s ease;
        }

        .hero-image-container:hover {
            transform: perspective(1000px) rotateY(0deg);
        }

        /* How It Works */
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        .step-card {
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: var(--sa-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(111, 66, 193, 0.3);
        }

        .step-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 0.75rem;
        }

        .step-desc {
            color: #6c757d;
            line-height: 1.6;
        }

        .step-connector {
            position: absolute;
            top: 30px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--sa-accent-strong), transparent);
        }

        /* Features Section */
        .features-section {
            background: linear-gradient(180deg, #ffffff 0%, #f8f4ff 100%);
            padding: 100px 0;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.4s ease;
            border: 1px solid rgba(111, 66, 193, 0.1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--sa-gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(111, 66, 193, 0.15);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--sa-accent);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            background: var(--sa-gradient);
            transform: scale(1.1);
        }

        .feature-card:hover .feature-icon i {
            color: white;
        }

        .feature-icon i {
            color: var(--sa-primary);
            transition: color 0.3s ease;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 0.75rem;
        }

        .feature-desc {
            color: #6c757d;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .feature-badge {
            display: inline-block;
            background: var(--sa-accent);
            color: var(--sa-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        /* Benefits Section */
        .benefits-section {
            background: var(--sa-gradient);
            padding: 80px 0;
            color: white;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .benefit-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .benefit-text h5 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .benefit-text p {
            opacity: 0.9;
            margin: 0;
            font-size: 0.95rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #f8f4ff 0%, #ede5ff 100%);
            padding: 100px 0;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: #1a1a2e;
            color: white;
            padding: 3rem 0 1.5rem;
        }

        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
        }

        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
        .delay-6 { animation-delay: 0.6s; }

        /* Responsive */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-image-container {
                transform: none;
                margin-top: 3rem;
            }
            .step-connector {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            .section-title {
                font-size: 2rem;
            }
            .hero-cta {
                flex-direction: column;
            }
            .btn-cta-primary, .btn-cta-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_PATH ?>">
                <div class="logo-box"><img src="<?= IMG_LOGO ?>" alt="StudyAid Logo"></div>
                <span class="brand-name">StudyAid</span>
            </a>
            <div class="d-flex gap-2">
                <a class="btn btn-signup rounded-pill" href="<?= BASE_PATH ?>auth/register" role="button">Sign up</a>
                <a class="btn btn-login rounded-pill" href="<?= BASE_PATH ?>auth/login" role="button">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" style="padding-top: 100px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title fade-in-up">
                        Transform Your Learning with <span class="highlight">AI-Powered</span> Study Tools
                    </h1>
                    <p class="hero-subtitle fade-in-up delay-1">
                        Upload your documents and let StudyAid's intelligent AI create personalized quizzes, flashcards, summaries, and more. Study smarter, not harder.
                    </p>
                    <div class="hero-cta fade-in-up delay-2">
                        <a href="<?= BASE_PATH ?>auth/register" class="btn btn-cta-primary">
                            <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
                        </a>
                        <a href="#features" class="btn btn-cta-secondary">
                            <i class="bi bi-play-circle me-2"></i>See How It Works
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-image fade-in-up delay-3">
                    <div class="hero-image-container">
                        <div class="text-center">
                            <div class="d-flex justify-content-center gap-3 mb-4">
                                <div class="feature-icon" style="width: 60px; height: 60px;">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="feature-icon" style="width: 60px; height: 60px;">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                                <div class="feature-icon" style="width: 60px; height: 60px;">
                                    <i class="bi bi-cpu"></i>
                                </div>
                                <div class="feature-icon" style="width: 60px; height: 60px;">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                                <div class="feature-icon" style="width: 60px; height: 60px;">
                                    <i class="bi bi-lightbulb"></i>
                                </div>
                            </div>
                            <h4 style="color: var(--sa-primary); font-weight: 600;">Document → AI → Study Materials</h4>
                            <p class="text-muted mb-4">Upload any document and get instant study tools</p>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <i class="bi bi-patch-question-fill text-primary" style="font-size: 1.5rem;"></i>
                                        <div class="small mt-1">Quizzes</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <i class="bi bi-card-text text-success" style="font-size: 1.5rem;"></i>
                                        <div class="small mt-1">Flashcards</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <i class="bi bi-chat-dots-fill text-info" style="font-size: 1.5rem;"></i>
                                        <div class="small mt-1">AI Chatbot</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <i class="bi bi-journal-text text-warning" style="font-size: 1.5rem;"></i>
                                        <div class="small mt-1">Summaries</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5" id="how-it-works" style="background: white;">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">How It Works</h2>
                <p class="section-subtitle">Get started in just 3 simple steps</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="step-card fade-in-up">
                        <div class="step-number">1</div>
                        <h4 class="step-title">Upload Your Document</h4>
                        <p class="step-desc">Upload any PDF, Word document, or image. Our OCR technology extracts text from any format.</p>
                        <div class="step-connector d-none d-md-block"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in-up delay-2">
                        <div class="step-number">2</div>
                        <h4 class="step-title">AI Processes Content</h4>
                        <p class="step-desc">Our advanced AI powered by Google Gemini analyzes your content and understands the key concepts.</p>
                        <div class="step-connector d-none d-md-block"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in-up delay-4">
                        <div class="step-number">3</div>
                        <h4 class="step-title">Start Learning</h4>
                        <p class="step-desc">Access auto-generated quizzes, flashcards, summaries, notes, and more. Track your progress!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Powerful Learning Features</h2>
                <p class="section-subtitle">Everything you need to study effectively, all powered by AI</p>
            </div>
            <div class="row g-4">
                <!-- Quiz -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="bi bi-patch-question-fill"></i>
                        </div>
                        <h4 class="feature-title">Smart Quiz Generator</h4>
                        <p class="feature-desc">Create customized quizzes with multiple question types: Multiple Choice, True/False, Short Answer, and Long Answer. Choose Bloom's Taxonomy levels for each type.</p>
                        <span class="feature-badge">AI-Powered</span>
                    </div>
                </div>
                
                <!-- Flashcards -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up delay-1">
                        <div class="feature-icon">
                            <i class="bi bi-card-text"></i>
                        </div>
                        <h4 class="feature-title">Interactive Flashcards</h4>
                        <p class="feature-desc">Auto-generate flashcard sets from your documents or create them manually. Flip through cards with keyboard shortcuts for efficient memorization.</p>
                        <span class="feature-badge">Flip & Learn</span>
                    </div>
                </div>
                
                <!-- Chatbot -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up delay-2">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots-fill"></i>
                        </div>
                        <h4 class="feature-title">AI Study Chatbot</h4>
                        <p class="feature-desc">Ask questions about your documents and get instant, contextual answers. Like having a personal tutor available 24/7.</p>
                        <span class="feature-badge">Ask Anything</span>
                    </div>
                </div>
                
                <!-- Homework Helper -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up delay-3">
                        <div class="feature-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h4 class="feature-title">Homework Helper</h4>
                        <p class="feature-desc">Upload homework questions as images or PDFs and get step-by-step AI-powered solutions and explanations.</p>
                        <span class="feature-badge">Problem Solver</span>
                    </div>
    </div>
    
                <!-- Summary & Notes -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up delay-4">
                        <div class="feature-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <h4 class="feature-title">Auto Summaries & Notes</h4>
                        <p class="feature-desc">Generate concise summaries and detailed study notes from lengthy documents. Focus on what matters most.</p>
                        <span class="feature-badge">Save Time</span>
                    </div>
                </div>
                
                <!-- Mind Map -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up delay-5">
                        <div class="feature-icon">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <h4 class="feature-title">Visual Mind Maps</h4>
                        <p class="feature-desc">Automatically create visual mind maps from your content. See the big picture and understand how concepts connect.</p>
                        <span class="feature-badge">Visualize</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <h2 class="display-5 fw-bold mb-4">Why Choose StudyAid?</h2>
                    <p class="lead opacity-90">Join thousands of students who are studying smarter with AI-powered tools.</p>
                </div>
                <div class="col-lg-7">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </div>
                                <div class="benefit-text">
                                    <h5>Save Hours of Time</h5>
                                    <p>Generate study materials in seconds, not hours</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="bi bi-bullseye"></i>
                                </div>
                                <div class="benefit-text">
                                    <h5>Personalized Learning</h5>
                                    <p>Materials tailored to your specific documents</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div class="benefit-text">
                                    <h5>Track Progress</h5>
                                    <p>Monitor quiz scores and learning improvement</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="bi bi-cloud-check-fill"></i>
                                </div>
                                <div class="benefit-text">
                                    <h5>Access Anywhere</h5>
                                    <p>Your study materials are always in the cloud</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </div>
                                <div class="benefit-text">
                                    <h5>Any Document Format</h5>
                                    <p>PDF, Word, PowerPoint, images - we support all</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="benefit-text">
                                    <h5>Secure & Private</h5>
                                    <p>Your documents are encrypted and protected</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Transform Your Study Routine?</h2>
            <p class="cta-subtitle">Join StudyAid today and experience the future of learning</p>
            <a href="<?= BASE_PATH ?>auth/register" class="btn btn-cta-primary btn-lg">
                <i class="bi bi-rocket-takeoff me-2"></i>Start Learning for Free
            </a>
            <p class="mt-3 text-muted">
                <i class="bi bi-check-circle text-success me-1"></i>No credit card required
                <span class="mx-2">•</span>
                <i class="bi bi-check-circle text-success me-1"></i>Free to get started
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="footer-brand d-flex align-items-center gap-2">
                        <img src="<?= IMG_LOGO ?>" alt="StudyAid" style="width: 40px; height: 40px;">
                        <span>StudyAid</span>
                    </div>
                    <p class="footer-text">Your AI-powered learning companion. Transform any document into interactive study materials.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="footer-text mb-2">Powered by</p>
                    <div class="d-flex gap-3 justify-content-md-end align-items-center">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="bi bi-google me-1"></i>Google Gemini AI
                        </span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="footer-text mb-0">&copy; <?= date('Y') ?> StudyAid. All rights reserved.</p>
        </div>
    </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
