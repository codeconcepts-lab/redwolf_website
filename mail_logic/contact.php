<?php
// Set response type to JSON for our JavaScript frontend
header('Content-Type: application/json');

// Define recipient email (Where you want to receive the messages)
$recipient_email = "contact@myscooty.ng";
$subject = "New Contact Form Submission - Scooty XP Website";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and validate inputs
    // Using filter_var to prevent malicious script injections
    $name = filter_var(trim($_POST["Name"]), FILTER_SANITIZE_STRING);
    $phone = filter_var(trim($_POST["Phone"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["Email"]), FILTER_SANITIZE_EMAIL);
    $message = filter_var(trim($_POST["Message"]), FILTER_SANITIZE_STRING);

    // Check required fields are not empty
    if (empty($name) || empty($phone) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Please fill out all fields correctly."]);
        exit;
    }

    // We use htmlspecialchars() to ensure any special characters in the message don't break the HTML layout.
    $safe_name = htmlspecialchars($name);
    $safe_phone = htmlspecialchars($phone);
    $safe_email = htmlspecialchars($email);
    $safe_message = nl2br(htmlspecialchars($message)); // nl2br converts line breaks to <br> tags

    // Build the HTML Email Body
    $email_content = '
    <html>
    <head>
      <title>New Contact Form Submission</title>
    </head>
    <body style="background-color: #f4f5f6; font-family: Helvetica, Arial, sans-serif; margin: 0; padding: 40px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td align="center">
            <!-- Main Content Card -->
            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
              
              <!-- Header Section -->
              <tr>
                <td align="center" style="padding-bottom: 30px; border-bottom: 1px solid #eaf0f6;">
                  <h1 style="color: #33475b; margin: 0; font-size: 28px; font-weight: bold;">Scooty XP</h1>
                  <h2 style="color: #33475b; margin: 20px 0 0 0; font-size: 22px; font-weight: normal;">New submission on <strong>Scooty XP</strong> Form</h2>
                </td>
              </tr>
              
              <!-- Data Section -->
              <tr>
                <td style="padding: 40px;">
                  
                  <div style="margin-bottom: 20px;">
                    <p style="margin: 0 0 5px 0; font-weight: bold; color: #516f90; font-size: 15px; text-transform: lowercase;">name:</p>
                    <p style="margin: 0; color: #33475b; font-size: 16px;">' . $safe_name . '</p>
                  </div>
                  
                  <div style="margin-bottom: 20px;">
                    <p style="margin: 0 0 5px 0; font-weight: bold; color: #516f90; font-size: 15px; text-transform: lowercase;">phone:</p>
                    <p style="margin: 0; color: #33475b; font-size: 16px;">' . $safe_phone . '</p>
                  </div>
                  
                  <div style="margin-bottom: 20px;">
                    <p style="margin: 0 0 5px 0; font-weight: bold; color: #516f90; font-size: 15px; text-transform: lowercase;">email:</p>
                    <a href="mailto:' . $safe_email . '" style="margin: 0; color: #0056b3; font-size: 16px; text-decoration: none;">' . $safe_email . '</a>
                  </div>
                  
                  <div style="margin-bottom: 30px;">
                    <p style="margin: 0 0 5px 0; font-weight: bold; color: #516f90; font-size: 15px; text-transform: lowercase;">message:</p>
                    <p style="margin: 0; color: #33475b; font-size: 16px; line-height: 1.6;">' . $safe_message . '</p>
                  </div>

                </td>
              </tr>
              
              <!-- Footer Button -->
              <tr>
                <td align="center" style="padding-top: 20px;">
                   <a href="https://myscooty.ng" style="background-color: #425b76; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; display: inline-block;">Go to Website</a>
                </td>
              </tr>

            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>';

    // To send HTML mail, the Content-type header must be set
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    // Additional headers - Use a constant domain email to avoid spam filters
    $headers .= "From: Scooty XP Contact Form <noreply@myscooty.ng>\r\n";

    // Reply-To allows you to hit "Reply" in your email client and go to the user
    $headers .= "Reply-To: $email\r\n";

    // Send the email using cPanel's native mail function
    if (mail($recipient_email, $subject, $email_content, $headers)) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Message sent successfully!"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to send the message. Please ensure mail services are configured properly on your cPanel."]);
    }

} else {
    // Block non-POST requests
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>