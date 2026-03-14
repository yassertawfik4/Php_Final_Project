<!DOCTYPE html>
<html>
<head>
    <title>Forget Password - Cafeteria</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f4f6fb;
            --surface: #ffffff;
            --text: #182230;
            --muted: #667085;
            --brand: #0f5ea8;
            --brand-strong: #0a4681;
            --accent: #e7a535;
            --radius-md: 12px;
            --shadow-soft: 0 10px 30px rgba(15, 26, 45, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, rgba(15, 94, 168, 0.05) 0%, rgba(231, 165, 53, 0.05) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 26, 45, 0.12);
            background: white;
            max-width: 900px;
            width: 100%;
        }

        .reset-brand-section {
            background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .reset-brand-section h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 20px 0;
            color: white;
        }

        .reset-brand-section p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            line-height: 1.6;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .reset-form-section {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .reset-form-section h3 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0 0 10px 0;
            color: #101828;
        }

        .reset-subtitle {
            color: #667085;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .form-group-reset {
            margin-bottom: 24px;
        }

        .form-group-reset label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: #344054;
            margin-bottom: 8px;
        }

        .form-group-reset input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #dbe4ee;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group-reset input:focus {
            outline: none;
            border-color: #0f5ea8;
            box-shadow: 0 0 0 4px rgba(15, 94, 168, 0.1);
            background: #f9fbff;
        }

        .form-group-reset input::placeholder {
            color: #a1afc9;
        }

        .btn-reset {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 94, 168, 0.25);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .reset-footer-links {
            margin-top: 24px;
            text-align: center;
            font-size: 0.9rem;
        }

        .reset-footer-links a {
            color: #0f5ea8;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .reset-footer-links a:hover {
            color: #0a4681;
            text-decoration: underline;
        }

        .error-alert {
            background: #fef3f2;
            border: 2px solid #f6d0d4;
            color: #b42318;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .reset-wrapper {
                grid-template-columns: 1fr;
            }

            .reset-brand-section {
                padding: 40px 30px;
            }

            .reset-form-section {
                padding: 40px 30px;
            }

            .reset-brand-section h2 {
                font-size: 1.8rem;
            }

            .brand-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }

            .reset-form-section h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

<div class="reset-wrapper">
    <!-- Brand Section -->
    <div class="reset-brand-section">
        <div class="brand-icon"></div>
        <h2>Secure Access</h2>
        <p>Recover your account and regain access to your orders and preferences.</p>
    </div>

    <!-- Reset Form Section -->
    <div class="reset-form-section">
        <h3>Forgot Your Password?</h3>
        <p class="reset-subtitle">Enter your email to find your account</p>

        <form action="reset_password.php" method="POST">
            <div class="form-group-reset">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    placeholder="Enter your email" 
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group-reset">
                <label for="password">New Password</label>
                <input 
                    type="password" 
                    id="password"
                    name="password" 
                    placeholder="Create a strong password" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <div class="form-group-reset">
                <label for="confirm">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm"
                    name="confirm_password" 
                    placeholder="Confirm your password" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn-reset">
                Reset Password
            </button>
        </form>

        <div class="reset-footer-links">
            <p>Remember your password? <a href="login.php">Sign in here</a></p>
        </div>
    </div>
</div>

</body>
</html>