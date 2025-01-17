<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Web Push Notifications</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
        }

        .card {
            margin-top: 2rem;
            padding: 2rem;
        }

        .btn-custom {
            background-color: #ff9900;
            color: white;
            border-radius: 4px;
        }

        .btn-custom:hover {
            background-color: #e68a00;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Web Push Notifications</h1>

        <div class="card shadow-lg">
            <div class="card-body">
                <!-- Allow Notification Button -->
                <button id="allow-notifications-btn" class="btn btn-custom btn-lg">Allow Notifications</button>

                <!-- Notification Form -->
                <form id="notification-form" class="mt-4">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="body">Message:</label>
                        <textarea id="body" name="body" class="form-control" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom btn-lg">Send Notification</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        const firebaseConfig = {
            apiKey: "",
            authDomain: "",
            projectId: "",
            storageBucket: "",
            messagingSenderId: "",
            appId: "",
            measurementId: ""
        };

        firebase.initializeApp(firebaseConfig);

        const messaging = firebase.messaging();

        messaging.onMessage(function(payload) {
            console.log('Message received in foreground: ', payload);
            const title = payload.notification.title;
            const options = {
                body: payload.notification.body,
                icon: payload.notification.icon,
            };
            // Show notification in the foreground
            new Notification(title, options);
        });

        // Handle Allow Notifications button
        document.getElementById('allow-notifications-btn').addEventListener('click', () => {
            messaging.requestPermission()
                .then(() => messaging.getToken())
                .then((token) => {
                    console.log('FCM Token:', token);

                    // Send token to the server
                    fetch('/save-token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                        body: JSON.stringify({
                            token: token
                        }),
                    }).then(response => {
                        if (response.ok) {
                            alert('Notifications enabled successfully!');
                        } else {
                            alert('Failed to save token.');
                        }
                    });
                })
                .catch((error) => {
                    console.error('Permission denied:', error);
                    alert('Failed to enable notifications.');
                });
        });

        // Handle Notification Form Submission
        document.getElementById('notification-form').addEventListener('submit', (e) => {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const body = document.getElementById('body').value;

            fetch('/send-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    title: title,
                    body: body
                }),
            }).then(response => {
                if (response.ok) {
                    alert('Notification sent successfully!');
                } else {
                    alert('Failed to send notification.');
                }
            });
        });
    </script>
</body>

</html>
