<?php
if($_POST["action"]=="login"){
    // Initialize the session
    session_name("DeveloperIDSession");
    session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        http_response_code(200);
        die();
    }

// Include config file
    require_once "../configuration.php";

// Define variables and initialize with empty values
    $username = $password = "";
    $username_err = $password_err = "";
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        http_response_code(403);
        die();
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        http_response_code(403);
        die();
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if($stmt = mysqli_prepare($loginlink, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Redirect user to welcome page
                            http_response_code(200);
                            die();
                        } else{
                            // Display an error message if password is not valid
                            http_response_code(403);
                            die();
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    http_response_code(403);
                    die();;
                }
            } else{
                http_response_code(500);
                die();
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            http_response_code(503);
            die(mysqli_stmt_error($stmt)." ".mysqli_error($loginlink));
        }
    }

    // Close connection
    mysqli_close($loginlink);
}
if($_POST["action"]=="logout"){
    session_name("DeveloperIDSession");
    session_start();
    $_SESSION = array();
    session_destroy();
    http_response_code(200);
    die();
}