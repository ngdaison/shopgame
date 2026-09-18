<x-app-layout pageTitle="404 - Trang không tìm thấy" :hideCustomScripts="true">
    <div class="error-page-flex-container">
        <div class="error-card">
            <div class="error-code">404</div>
            <div class="error-title">KHÔNG TÌM THẤY TRANG</div>
            
            <p class="error-message">
                Trang bạn truy cập đã không được tìm thấy<br>
            </p>
            
            <div class="button-container">
                <a href="/" class="btn-home">Về trang chủ</a>
            </div>
        </div>
    </div>

    @push('css')
    <style>
        /* Base styles */
        html, body {
            height: 100% !important;
            margin: 0;
            padding: 0;
        }



        /* Layout structure */
        body > div.app-wrapper, 
        #page_layout {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .content-wrapper {
            flex-grow: 1 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .page-content, #content_layout {
            flex-grow: 1 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .error-page-flex-container {
            display: flex;
            flex-grow: 1;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .error-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 480px;
            width: 100%;
            text-align: center;
            margin-bottom: 20px; /* Space for footer on small screens */
        }

        .error-code {
            font-size: 80px;
            font-weight: 900;
            color: #000;
            line-height: 1;
            margin-bottom: 10px;
            font-family: 'Inter', sans-serif;
        }

        .error-title {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .error-message {
            font-size: 15px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .btn-home {
            display: inline-block;
            background-color: #2563eb;
            color: white !important;
            padding: 12px 35px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }

        .btn-home:hover {
            background-color: #1d4ed8;
        }

        /* Desktop adjustments */
        @media (min-width: 768px) {
            .error-card {
                padding: 60px 40px;
                margin-top: -40px;
            }
            .error-code {
                font-size: 110px;
            }
            .error-title {
                font-size: 28px;
            }
            .error-message {
                font-size: 17px;
            }
            .btn-home {
                padding: 14px 45px;
                font-size: 16px;
            }
        }

        /* Dark mode support */
        .dark .error-card {
            background: #1e293b;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .dark .error-code { color: #f8fafc; }
        .dark .error-title { color: #f1f5f9; }
        .dark .error-message { color: #cbd5e1; }
    </style>
    @endpush
</x-app-layout>
