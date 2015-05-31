<?php
/*
The purpose of this file is to ensure that the proper data validation 
and security measures are taken before a user is allowed to view the website.
Variable Definitions:
$pageName: Holds the current page name or file name.
$data:  holds user input during data sanitation process
$connection: holds connection to 
$email: Email address entered by user
$password: Password entered by user
$valid: holds true or false value to determine if the user entered a valid data for the email and password field
$conn: equals to $connection variable. value returned from function
$findUserQuery: query syntax for select statement
$result: holds query results
$pwmatch: holds a true or false value representating if the password the user entered was the same as the database that is in the password.
$_SESSION["email"]: holds users email 
$_SESSION["fname"]: holds users first name
$_SESSION["lname"]: hods users last name
$errorMsg: Holds current error message
$emailErr: Holds email error message
$passErr: Holds pass error message
msqlErrorCode: Holds the error code for the mysql error
msqlErrorStatus: Holds the message for the mysql error
mysqlError: Holds true or false representing if it is a mysql error or not
mysqlErrorType: Holds the type of mysqlError
*/
include("../Includes/config.php");
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
$_SESSION['PageName'] = 'Login';
$mysqlError = FALSE;
$mysqlErrorType ='';
//check if login was requested by the server
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	//gets rid of dangerous characters
	function clean($data) 
	{	
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
	
	$valid = TRUE;
	
	function loginFailure($errorNumber)
	{
		$_SESSION['LoginError'] = $errorNumber;
		header("Location: http://brserviceswap.isys489.com");
		exit;
	}
	
	//validates emaill; if email was not filled in, sends user back to homepage with error 2
	if(empty($_POST["email"])) 
    {
		loginFailure(1);
	
	} 
	
	//if email is invalid, sends user back to homepage with error 3
	if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) 
	{
		loginFailure(1);
	
	}
	
	$email = clean($_POST["email"]);
	
	//checks if email ends with 91swapraptor.com for employee account or if not for user account
	if(substr($email, -14)== "swapraptor.com")
	{
		$findUserQuery ="SELECT E.Email, L.Password, E.FName, E.LName, E.EmployeeID, E.Disabled, E.EmployeeType 
		FROM Employee E, EmployeePassword L WHERE E.EmployeeID = L.EmployeeID AND E.Email ='".$email. "' LIMIT 1";
	}
	else
	{
		$findUserQuery ="SELECT U.Email, L.Password, U.FName, U.LName, U.UserID, U.Status FROM User U, 
		UserPassword L WHERE U.UserID = L.UserID AND U.Email ='".$email."' LIMIT 1";
	}

	//if password is invalid, sends user back to homepage with error 4
	if(empty($_POST["pass"])) 
	{
		loginFailure(1);
	
	} 
	
	$password = clean($_POST["pass"]);
	if($valid)
	{
		//creates a connection to the database
		//$conn = connectToDb();
		$email = mysqli_real_escape_string($conn, $email);
	
		//executes query
		$result = mysqli_query($conn, $findUserQuery);
		if(!$result)
		{
			$msqlErrorCode = $mysqli_errno($conn);
			$msqlErrorStatus = $mysqli_error($conn);
		
			//$mysqlError=TRUE;
			//$mysqlErrorType='Query';
		
			sqlErrors($msqlErrorCode,$msqlErrorStatus,$pageName);
			loginFailure(3);
		
		}
		
		//checks if user exists
		if(mysqli_num_rows($result)!= 0)
		{
			$row = mysqli_fetch_array($result, MYSQL_NUM );
		
			/*stores results from query in the following variables*/
		
			$passwordDB = $row[1];
			$emailDB = $row[0];
			$disabled = $row[5];
		
			if ($disabled == 1)
			{
				
				mysqli_close($conn);
				loginFailure(2);
				
			}
			else if ($disabled == 2)
			{
				mysqli_close($conn);
				loginFailure(2);
			
			}
			else if ($disabled == 3)
			{
				mysqli_close($conn);
				loginFailure(2);
			
			}
			else if ($disabled == 1 && isset($row[6]))
			{
				mysqli_close($conn);
				loginFailure(2);
			
			}
	
			//verifying password, checks if the password is the same as the hashed password in the database
			$pwmatch = 0;
			if ($password == $passwordDB)
			{
				$pwmatch = 1;
			}
		
			//$pwmatch = password_verify($password, $passwordDB);
			//checks if password matches the password in the database; if so, session variables are created
			if($pwmatch == 1)
			{
				//starts session and initiates session variables
				//session_start();
		
				$_SESSION["email"] = $row[0];
				$_SESSION["fname"] = $row[2];
				$_SESSION["lname"] = $row[3];
				$_SESSION["id"] = $row[4];
			
				if (isset($row[6]))
				{
					$_SESSION["EmployeeType"] = $row[6];
				}
			
				$_SESSION['LoginError'] = 0;
				header("Location: http://brserviceswap.isys489.com");
			}
		}
		//if password does not match the database password, user is sent back with error 1
		else 
		{
			//echo $pwmatch . " DB Pass: " . $passwordDB . " Input Pass: " . $password . " DB Email: " . $emailDB . " Input Email: " . $email;
			mysqli_close($conn);
			loginFailure(1);
			
		}
	}
	//if username doesn't exist, user is sent back with error 1
	else
	{
		mysqli_close($conn);
		loginFailure(1);
	}
	
	mysqli_close($conn);
	exit;
	
	
}
?>
