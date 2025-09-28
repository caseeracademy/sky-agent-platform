<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky Agent Platform - Student Application Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-indigo-600">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Sky Agent Platform
                        </h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/admin" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
                        <i class="fas fa-user-shield mr-2"></i>Admin Panel
                    </a>
                    <a href="/agent" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                        <i class="fas fa-users mr-2"></i>Agent Portal
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg hero-pattern py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
                Sky Agent Platform
            </h1>
            <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-3xl mx-auto">
                Streamline student application management with our comprehensive platform designed for educational agents and administrators.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 inline-flex items-center justify-center">
                    <i class="fas fa-user-shield mr-2"></i>
                    Admin Dashboard
                </a>
                <a href="/agent" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition duration-300 inline-flex items-center justify-center">
                    <i class="fas fa-users mr-2"></i>
                    Agent Portal
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Platform Features</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Everything you need to manage student applications, track commissions, and streamline your educational consulting business.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card-hover bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                    <div class="text-indigo-600 text-4xl mb-4">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Application Management</h3>
                    <p class="text-gray-600">
                        Track student applications from submission to decision with comprehensive status updates and document management.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="card-hover bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                    <div class="text-green-600 text-4xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Commission Tracking</h3>
                    <p class="text-gray-600">
                        Monitor earnings and commissions with detailed analytics and automated payout calculations.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="card-hover bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Team Management</h3>
                    <p class="text-gray-600">
                        Manage agent teams with role-based access control and hierarchical permissions.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="card-hover bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                    <div class="text-purple-600 text-4xl mb-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Secure Document Storage</h3>
                    <p class="text-gray-600">
                        Safely store and manage student documents with secure access controls and audit trails.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="card-hover bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                    <div class="text-red-600 text-4xl mb-4">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Real-time Notifications</h3>
                    <p class="text-gray-600">
                        Stay updated with instant notifications for application status changes and important updates.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="card-hover bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                    <div class="text-yellow-600 text-4xl mb-4">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Advanced Analytics</h3>
                    <p class="text-gray-600">
                        Comprehensive reporting and analytics to track performance and make data-driven decisions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Platform Statistics</h2>
                <p class="text-xl text-gray-600">
                    Trusted by educational professionals worldwide
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-indigo-600 mb-2">500+</div>
                    <div class="text-gray-600">Active Agents</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">10,000+</div>
                    <div class="text-gray-600">Applications Processed</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-blue-600 mb-2">95%</div>
                    <div class="text-gray-600">Success Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-purple-600 mb-2">24/7</div>
                    <div class="text-gray-600">Platform Uptime</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Get Started?</h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                Join thousands of educational professionals who trust Sky Agent Platform for their student application management needs.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 inline-flex items-center justify-center">
                    <i class="fas fa-user-shield mr-2"></i>
                    Access Admin Panel
                </a>
                <a href="/agent" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition duration-300 inline-flex items-center justify-center">
                    <i class="fas fa-users mr-2"></i>
                    Agent Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Sky Agent Platform
                    </h3>
                    <p class="text-gray-400">
                        Empowering educational agents with comprehensive application management tools.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/admin" class="text-gray-400 hover:text-white transition duration-300">Admin Panel</a></li>
                        <li><a href="/agent" class="text-gray-400 hover:text-white transition duration-300">Agent Portal</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Documentation</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <div class="text-gray-400">
                        <p><i class="fas fa-envelope mr-2"></i>support@skyagent.com</p>
                        <p><i class="fas fa-phone mr-2"></i>+1 (555) 123-4567</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Sky Agent Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
