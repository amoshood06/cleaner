<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanie - Professional Cleaning Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#A086A3',
                        secondary: '#ffc107',
                        light: '#f8f9fa',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .shape-1 {
            position: absolute;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(0, 193, 112, 0.1);
            z-index: -1;
        }
        .shape-2 {
            position: absolute;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: rgba(255, 193, 7, 0.1);
            z-index: -1;
        }
        .shape-3 {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            background-color: rgba(0, 193, 112, 0.1);
            transform: rotate(45deg);
            z-index: -1;
        }

        /* WhatsApp Floating Button Styles */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 100;
        }
        
        .whatsapp-button {
            width: 60px;
            height: 60px;
            background-color: #25D366;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .whatsapp-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.4);
        }
        
        /* Bouncing Animation */
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        
        .bounce {
            animation: bounce 2s infinite;
        }
        
        /* Pulse Animation for additional attention */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
    
    </style>
    

</head>
<body class="bg-white overflow-x-hidden w-full">
<div id="google_translate_element" class="w-full flex justify-center"></div>

<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL}, 'google_translate_element');
}
</script>

<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <!-- Decorative Shapes -->
    <div class="shape-1 top-20 left-10"></div>
    <div class="shape-2 top-40 right-20"></div>
    <div class="shape-3 top-60 left-40"></div>
    <div class="shape-1 top-80 right-40"></div>
    <div class="shape-2 bottom-20 left-20"></div>
    <div class="shape-3 bottom-40 right-10"></div>

    <!-- Header -->
    <header class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center">
            <img src="logo.png" alt="Logo" class="w-[50px]">
        </div>
  <!-- Hamburger Menu Button (visible only on mobile) -->
<button id="menu-toggle" class="block md:hidden text-primary focus:outline-none">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
    </svg>
</button>

<!-- Navigation Menu -->
<nav id="menu" class="hidden flex-col md:flex md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-8 absolute md:static top-14 left-0 w-full md:w-auto bg-white md:bg-transparent shadow-md md:shadow-none z-50">
    <a href="#" class="text-primary font-medium px-4 py-2 md:p-0">Home</a>
    <a href="#" class="text-gray-600 hover:text-primary transition px-4 py-2 md:p-0">About</a>
    <a href="#" class="text-gray-600 hover:text-primary transition px-4 py-2 md:p-0">Services</a>
    <a href="#" class="text-gray-600 hover:text-primary transition px-4 py-2 md:p-0">Contact</a>
    <a href="login.php" class="text-gray-600 hover:text-primary transition px-4 py-2 md:p-0">Account</a>
</nav>


        <a href="login.php" class="hidden md:block">
            <button class="bg-primary text-white px-6 py-2 rounded-full font-medium hover:bg-opacity-90 transition">Account</button>
        </a>
    </header>

    <!-- Hero Section -->
    <section class="container mx-auto px-4 py-12 md:py-20 relative">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <div class="text-secondary font-medium mb-2">Cleaning Service</div>
                <h1 class="text-4xl md:text-5xl font-bold mb-6">We Are <span class="text-primary">Certified</span> Company</h1>
                <p class="text-gray-600 mb-8">Our professional and experienced cleaning staff will make your home or office spotless and fresh.</p>
                <div class="bg-white shadow-lg rounded-lg p-4 inline-flex items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Call for appointment</p>
                        <p class="text-primary font-bold">+21 263 994 4817</p>
                    </div>
                </div>
            </div>
            <div class="md:w-1/2 relative">
                <div class="bg-primary bg-opacity-10 rounded-full h-80 w-80 mx-auto relative">
                    <img src="clean.png" alt="Cleaning Professional" class="absolute bottom-0 right-0 h-96">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Preview -->
    <section class="container mx-auto px-4 py-12 md:py-20">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 md:mb-0">Making Your House <br>As Good As New</h2>
            <a href="#" class="text-primary font-medium flex items-center">
                See all
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Service 1 -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Apartment Cleaning</h3>
                <p class="text-gray-600">We provide thorough cleaning services for apartments of all sizes.</p>
            </div>
            <!-- Service 2 -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                <div class="bg-secondary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Resort Cleaning</h3>
                <p class="text-gray-600">Keep your resort spotless and welcoming for all your guests.</p>
            </div>
            <!-- Service 3 -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                <div class="bg-primary bg-opacity-10 p-4 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Office Cleaning</h3>
                <p class="text-gray-600">Professional cleaning services for offices and commercial spaces.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="container mx-auto px-4 py-12 md:py-20">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <img src="clean1.png" alt="Cleaning Professional" class="rounded-xl">
            </div>
            <div class="md:w-1/2 md:pl-12">
                <div class="text-secondary font-medium mb-2">About Us</div>
                <h2 class="text-3xl md:text-4xl font-bold mb-6">We are best cleaning company since 2006</h2>
                <p class="text-gray-600 mb-8">Lorem ipsum sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                <div class="flex items-center mb-8">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">15 Years</p>
                        <p class="text-gray-600 text-sm">Experience</p>
                    </div>
                </div>
                <button class="bg-primary text-white px-6 py-3 rounded-full font-medium hover:bg-opacity-90 transition">Learn More</button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mx-auto px-4 py-12 md:py-20 bg-gray-50 rounded-3xl">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-4">We will make any place <br>neat & clean</h2>
        <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">Cleanie is a professional cleaning company focused on providing the best cleaning services for your home and office at affordable prices.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <!-- Feature 1 -->
            <div class="bg-white p-6 rounded-xl shadow-md flex">
                <div class="bg-primary bg-opacity-10 p-4 rounded-full h-16 w-16 flex items-center justify-center mr-4 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Customer Focused Reviews</h3>
                    <p class="text-gray-600">We take customer feedback seriously and continuously improve our services based on your input.</p>
                </div>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white p-6 rounded-xl shadow-md flex">
                <div class="bg-secondary bg-opacity-10 p-4 rounded-full h-16 w-16 flex items-center justify-center mr-4 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Regular & Monthly Cleaning</h3>
                    <p class="text-gray-600">Schedule regular cleaning services on a weekly, bi-weekly, or monthly basis for consistent cleanliness.</p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-center">
            <button class="bg-primary text-white px-6 py-3 rounded-full font-medium hover:bg-opacity-90 transition">Learn More</button>
        </div>
        
        <div class="mt-16 flex items-center justify-center">
            <div class="bg-primary rounded-full p-4 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-4xl font-bold text-primary">26,846</p>
                <p class="text-gray-600">Happy Clients</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="container mx-auto px-4 py-12 md:py-20">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-4">Pricing and Plan</h2>
        <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">Lorem ipsum sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Basic Plan -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                <h3 class="text-xl font-bold mb-6 text-center">BASIC</h3>
                <div class="text-center mb-6">
                    <span class="text-4xl font-bold">$49</span>
                    <span class="text-gray-600">/mo</span>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Thorough Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>2 Bedroom Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>1 Bathroom Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Window Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Dusting Room</span>
                    </li>
                </ul>
                <button class="w-full bg-white border border-primary text-primary px-6 py-3 rounded-full font-medium hover:bg-primary hover:text-white transition">Book Now</button>
            </div>
            
            <!-- Premium Plan -->
            <div class="bg-gradient-to-b from-secondary to-primary rounded-xl shadow-lg p-8 hover:shadow-xl transition transform -translate-y-4">
                <h3 class="text-xl font-bold mb-6 text-center text-white">PREMIUM</h3>
                <div class="text-center mb-6">
                    <span class="text-4xl font-bold text-white">$99</span>
                    <span class="text-white">/mo</span>
                </div>
                <ul class="space-y-4 mb-8 text-white">
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Thorough Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>3 Bedroom Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>2 Bathroom Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Window Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Dusting Room</span>
                    </li>
                </ul>
                <a href="booking.php">
                <button class="w-full bg-white text-primary px-6 py-3 rounded-full font-medium hover:bg-gray-100 transition">Book Now</button>
                </a>
            </div>
            
            <!-- Medium Plan -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                <h3 class="text-xl font-bold mb-6 text-center">MEDIUM</h3>
                <div class="text-center mb-6">
                    <span class="text-4xl font-bold">$69</span>
                    <span class="text-gray-600">/mo</span>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Thorough Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>3 Bedroom Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>2 Bathroom Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Window Cleaning</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Dusting Room</span>
                    </li>
                </ul>
                <a href="booking.php">
                <button class="w-full bg-white border border-primary text-primary px-6 py-3 rounded-full font-medium hover:bg-primary hover:text-white transition">Book Now</button>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="container mx-auto px-4 py-12 md:py-20">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 md:mb-0">What our client says</h2>
            <a href="#" class="text-primary font-medium flex items-center">
                See all
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        
        <div class="relative">
            <!-- Testimonial -->
            <div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl mx-auto">
                <div class="flex items-center mb-6">
                    <img src="/placeholder.svg?height=80&width=80" alt="Client" class="w-16 h-16 rounded-full mr-4">
                    <div>
                        <div class="flex mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <h4 class="font-bold">Jane Foster</h4>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"I had a great service and experience. Really made my life easier and I like how quick they cleaned my apartment. Would definitely recommend!"</p>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute -top-4 -left-4 bg-primary bg-opacity-10 rounded-full h-12 w-12"></div>
            <div class="absolute top-1/2 -right-4 bg-secondary bg-opacity-10 rounded-full h-8 w-8"></div>
            <div class="absolute -bottom-4 left-1/4 bg-primary bg-opacity-10 rounded-full h-10 w-10"></div>
            
            <!-- Client Avatars -->
            <div class="absolute -bottom-8 right-1/4">
                <img src="/placeholder.svg?height=50&width=50" alt="Client" class="w-10 h-10 rounded-full border-2 border-white">
            </div>
            <div class="absolute top-1/3 -left-12">
                <img src="/placeholder.svg?height=50&width=50" alt="Client" class="w-10 h-10 rounded-full border-2 border-white">
            </div>
            <div class="absolute bottom-1/4 -right-12">
                <img src="/placeholder.svg?height=50&width=50" alt="Client" class="w-10 h-10 rounded-full border-2 border-white">
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="container mx-auto px-4 py-12 md:py-20">
        <div class="bg-gray-50 rounded-3xl p-8 md:p-12">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Get A quote</h2>
                    <p class="text-gray-600 mb-8">Fill out the form and our team will get back to you within 24 hours.</p>
                    <div class="relative">
                        <img src="clean2.png" alt="Cleaning Professional" class="rounded-xl">
                    </div>
                </div>
                <div class="md:w-1/2 md:pl-12">
                    <form>
                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 mb-2">Your Name</label>
                            <input type="text" id="name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="service" class="block text-gray-700 mb-2">Service Type</label>
                            <select id="service" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option>Select Service</option>
                                <option>Apartment Cleaning</option>
                                <option>Office Cleaning</option>
                                <option>Resort Cleaning</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="message" class="block text-gray-700 mb-2">Message</label>
                            <textarea id="message" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="bg-primary text-white px-8 py-3 rounded-full font-medium hover:bg-opacity-90 transition">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between mb-12">
                <div class="mb-8 md:mb-0">
                    <div class="flex items-center mb-4">
                        <img src="logo.png" alt="" class="w-[50px]">
                    </div>
                    <p class="text-gray-600 max-w-xs">Professional cleaning services for homes and offices.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h4 class="font-bold mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Home</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">About</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Services</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold mb-4">Services</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Apartment Cleaning</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Office Cleaning</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Resort Cleaning</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-primary transition">Window Cleaning</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold mb-4">Contact</h4>
                        <ul class="space-y-2">
                            <li class="text-gray-600">123 Cleaning St, City</li>
                            <li class="text-gray-600">+00 912 456 789</li>
                            <li class="text-gray-600">info@cleanie.com</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-6">
                <p class="text-center text-gray-600">© 2025 Cleanie. All rights reserved.</p>
            </div>
        </div>
        
        <!-- Chat Button -->
    <!-- Floating WhatsApp Button -->
    <div class="whatsapp-float">
        <div class="whatsapp-button bounce pulse" onclick="redirectToWhatsAppSupport()">
            <i class="fab fa-whatsapp"></i>
        </div>
    </div>

    <script>
         // WhatsApp integration function for product inquiries
         function redirectToWhatsApp(cylinderSize) {
            // Replace with your actual WhatsApp number
            const phoneNumber = "+2347018933739";
            const message = `Hello, I'm interested in buying a ${cylinderSize} gas cylinder. Please provide more information.`;
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // WhatsApp integration function for customer support
        function redirectToWhatsAppSupport() {
            // Replace with your actual WhatsApp support number (can be the same as above)
            const phoneNumber = "+212639944817";
            const message = "Hello, I need assistance with JumandiGas products/services.";
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // Stop bouncing animation after 10 seconds to avoid annoying the user
        setTimeout(() => {
            const whatsappButton = document.querySelector('.whatsapp-button');
            whatsappButton.classList.remove('bounce');
            // Keep the pulse animation for subtle attention
        }, 10000);
    </script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    </footer>
    <!-- JavaScript for Toggle -->
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('menu');

    menuToggle.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });
</script>
</body>
</html>