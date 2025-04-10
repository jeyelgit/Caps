<!DOCTYPE html>
<html>

<head>
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="../static/css-files/Register.css">

    <style>
        /* Add any additional styles here */
        .error {
            color: red;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .btn {
            border: 2px solid gray;
            color: gray;
            background-color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
        }

        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        #profilePicPreview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal {
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="right">
            <model-viewer src="../static/images/house.glb" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>
        </div>
        <div class="left">
            <h1>Create Your Account</h1>
            <form method="POST" action="signup-rec.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="userId">User ID (Optional)</label>
                    <input type="text" id="userId" name="userId" placeholder="Enter a unique user ID">
                </div>
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" placeholder="Enter your middle name">
                </div>
                <div class="form-group">
                    <label for="homeAddress">Home Address</label>
                    <input type="text" id="homeAddress" name="homeAddress" placeholder="Enter your home address">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="mobileNumber">Mobile Number</label>
                    <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="Enter your mobile number">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                </div>
                <div class="form-group">
                    <label for="profilePic">Profile Picture</label>
                    <div class="upload-btn-wrapper">
                        <button class="btn">Upload a file</button>
                        <input type="file" name="profilePic" id="profilePic" accept="image/*" onchange="previewProfilePic(event)" />
                        <img id="profilePicPreview" src="#" alt="Profile Picture Preview" style="display: none;">
                    </div>
                </div>
                <div class="form-options">
                    <label>
                        <input type="checkbox" name="signupTerms" id="signupTermsCheckbox" disabled>
                        I agree to the <button type="button" id="signupTermsButton" class="termsbutton">Terms and Conditions & Privacy Policy</button>
                    </label>
                </div>
                <div class="buttons">
                    <button type="submit" class="signup" id="signupSubmitButton" disabled>Sign up</button>
                    <a href="../index.php"><button type="button" class="login">Login</button></a>
                </div>
            </form>
            <div id="signupTermsModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" id="signupModalClose">&times;</span>
                    <h1>Terms and Conditions</h1>
                    <p>Welcome to RM Betis Furniture. By using our system and placing an order, you agree to the following terms and conditions:</p>
                    <h2>1. Downpayment Policy</h2>
                    <p>Before we process or accept your order, you are required to pay a 60% downpayment. The remaining balance must be settled upon completion of the order.</p>
                    <h2>2. No Cancellation Policy</h2>
                    <p>Once an order is placed, cancellations are not allowed. Please ensure that all details of your order are correct before confirming.</p>
                    <h2>3. Privacy Policy</h2>
                    <p>All personal information you provide in this system will be kept private and secure. We are committed to protecting your data and will not share it with third parties without your consent.</p>
                    <p>If you have any questions or concerns regarding these terms, please contact us for clarification.</p>
                    <label>
                        <input type="checkbox" id="signupModalAgreeCheckbox"> I agree to the terms and conditions
                    </label>
                </div>
            </div>
            <!-- <div class="terms">
                By signing up, you agree to the Terms and Conditions & Privacy Policy.
            </div>
        </div> -->
    </div>
    <a class="theme-toggle">
        <span class="entypo--switch1"></span>
    </a>

    <script>
        const toggleButton = document.getElementsByClassName('theme-toggle')[0]; // Access the first element
        const body = document.body;

        toggleButton.addEventListener('click', () => {
            // Toggle the 'dark-mode' class on the body
            body.classList.toggle('dark-mode');

            // Change button icon based on the current theme
            if (body.classList.contains('dark-mode')) {
                toggleButton.innerHTML = '<span class="entypo--switch1"></span>'; // Switch to dark mode icon
            } else {
                toggleButton.innerHTML = '<span class="entypo--switch2"></span>'; // Switch to light mode icon
            }
        });

        function previewProfilePic(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profilePicPreview');
                output.src = reader.result;
                output.style.display = 'block'; // Show the image
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        const signupTermsButton = document.getElementById('signupTermsButton');
        const signupTermsModal = document.getElementById('signupTermsModal');
        const signupModalClose = document.getElementById('signupModalClose');
        const signupModalAgreeCheckbox = document.getElementById('signupModalAgreeCheckbox');
        const signupTermsCheckbox = document.getElementById('signupTermsCheckbox');
        const signupSubmitButton = document.getElementById('signupSubmitButton');

        // Open the modal when the "Terms and Conditions" button is clicked
        signupTermsButton.addEventListener('click', () => {
            signupTermsModal.style.display = 'block';
        });

        // Close the modal when the "close" button is clicked
        signupModalClose.addEventListener('click', () => {
            signupTermsModal.style.display = 'none';
        });

        // Close the modal when clicking outside the modal content
        window.addEventListener('click', (event) => {
            if (event.target === signupTermsModal) {
                signupTermsModal.style.display = 'none';
            }
        });

        // Enable the form checkbox and "Sign up" button when the modal checkbox is checked
        signupModalAgreeCheckbox.addEventListener('change', () => {
            if (signupModalAgreeCheckbox.checked) {
                signupTermsCheckbox.checked = true;
                signupSubmitButton.disabled = false; // Enable the "Sign up" button
            } else {
                signupTermsCheckbox.checked = false;
                signupSubmitButton.disabled = true; // Disable the "Sign up" button
            }
        });

        // Enable/disable the "Sign up" button based on the form checkbox state
        signupTermsCheckbox.addEventListener('change', () => {
            signupSubmitButton.disabled = !signupTermsCheckbox.checked; // Enable/disable based on checkbox state
        });
    </script>
</body>

</html>
