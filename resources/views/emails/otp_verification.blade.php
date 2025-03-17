<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Code de Vérification OTP</title>

    <style>
        /* Global styles for the email */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        table {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .email-container {
            padding: 20px;
            background-color: #ffffff;
        }

        .email-header {
            text-align: center;
            padding: 30px 0;
            background-color: #007BFF;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .email-body {
            padding: 20px;
            text-align: center;
        }

        .otp {
            font-size: 32px;
            font-weight: bold;
            color: #007BFF;
            margin: 20px 0;
        }

        .message {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #888;
            padding: 20px 0;
            background-color: #f9f9f9;
        }

        .footer a {
            color: #007BFF;
            text-decoration: none;
        }

        /* Responsiveness */
        @media only screen and (max-width: 600px) {
            .email-header {
                font-size: 20px;
            }

            .otp {
                font-size: 28px;
            }

            .message {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td>
                <div class="email-container">
                    <!-- Email Header -->
                    <div class="email-header">
                        Code de Vérification
                    </div>

                    <!-- Email Body -->
                    <div class="email-body">
                        <p class="message">
                            Bonjour,<br>
                            Voici votre code de vérification à usage unique :
                        </p>
                        <div class="otp">
                            {{ $otp }}
                        </div>

                        <p class="message">
                            Ce code expire après 10 minutes. Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer ce message.
                        </p>

                        <p class="message">
                            Merci de votre confiance,<br>
                            L'équipe de {{ config('app.name') }}
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p>
                            Si vous n'avez pas demandé cette action, vous pouvez ignorer cet email.
                        </p>
                        <p>
                            <a href="{{ url('/') }}">Retour à notre site</a>
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
