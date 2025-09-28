<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky Agent Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">
                        Sky Agent Platform
                    </h1>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="/admin" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">
                        Admin
                    </a>
                    <a href="/agent" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition">
                        Agent
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                Student Application Management
            </h1>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                Streamline your educational consulting business with our comprehensive platform.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin" class="bg-white text-blue-600 px-6 py-3 rounded-md font-medium hover:bg-gray-50 transition">
                    Admin Dashboard
                </a>
                <a href="/agent" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-md font-medium hover:bg-white hover:text-blue-600 transition">
                    Agent Portal
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Key Features</h2>
                <p class="text-lg text-gray-600">
                    Everything you need to manage student applications efficiently
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-blue-600 text-3xl mb-4">📋</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Application Management</h3>
                    <p class="text-gray-600">
                        Track applications from submission to decision with comprehensive status updates.
                    </p>
                </div>

                <div class="card-hover bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-green-600 text-3xl mb-4">💰</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Commission Tracking</h3>
                    <p class="text-gray-600">
                        Monitor earnings and commissions with detailed analytics and automated calculations.
                    </p>
                </div>

                <div class="card-hover bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-purple-600 text-3xl mb-4">👥</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Team Management</h3>
                    <p class="text-gray-600">
                        Manage agent teams with role-based access control and hierarchical permissions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Get Started?</h2>
            <p class="text-lg text-gray-600 mb-8">
                Access your dashboard and start managing applications today.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin" class="bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 transition">
                    Admin Dashboard
                </a>
                <a href="/agent" class="bg-green-600 text-white px-6 py-3 rounded-md font-medium hover:bg-green-700 transition">
                    Agent Portal
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Sky Agent Platform</h3>
                <p class="text-gray-400 text-sm">
                    Empowering educational agents with comprehensive application management tools.
                </p>
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <p class="text-gray-400 text-sm">&copy; 2024 Sky Agent Platform. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
