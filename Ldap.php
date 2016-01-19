<?php

/*********************************************
 *
 * Library to authenticate to LDAP servers 
 * and retrieve user information from LDAP
 * Written by Elm� 2008
 *
 *********************************************/
require_once 'include/ldap/config.ldap.php';

function ldapConnectServer()
{
	global $AUTHCFG;
	
	$conn = @ldap_connect($AUTHCFG['ldap_host'],$AUTHCFG['ldap_port']);
	@ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3); // Try version 3.  Will fail and default to v2.

	//SEAN TSANG PATCH START - Trying to bind with highest privilege first.
        if (!empty($AUTHCFG['ldap_admin']) && !empty($AUTHCFG['ldap_adminpwd'])){
            if (!@ldap_bind($conn, $AUTHCFG['ldap_admin'], $AUTHCFG['ldap_adminpwd']))
         return NULL;
        }
        //if (!empty($AUTHCFG['ldap_username']))
        elseif (!empty($AUTHCFG['ldap_username']))
        //SEAN TSANG PATCH END -------------------------------------------------
	{
		if (!@ldap_bind($conn, $AUTHCFG['ldap_username'], $AUTHCFG['ldap_pass']))
			return NULL;
	} 
	else 
	{
		if (!@ldap_bind($conn)) //attempt an anonymous bind if no user/pass specified in config.php
			return NULL;
	}
	return $conn;
}

/**
 * Function to authenticate users via LDAP
 *
 * @param string $authUser -  Username to authenticate
 * @param string $authPW - Cleartext password
 * @return NULL on failure, user's info (in an array) on bind
 */
function ldapAuthenticate($authUser, $authPW) 
{
	global $AUTHCFG;
	
	if (empty($authUser) || empty($authPW)) 
		return false;
	
	$conn = ldapConnectServer();
	if ($conn == NULL)
		return false;
	
	$retval = false;
	$filter = $AUTHCFG['ldap_account'] . '=' . $authUser;
	$ident  = @ldap_search($conn, $AUTHCFG['ldap_basedn'], $filter);
	if ($ident) 
	{
		$result = @ldap_get_entries($conn, $ident);
		if ($result[0]) 
		{
			// dn is the LDAP path where the user was fond. This attribute is always returned.
			if (@ldap_bind( $conn, $result[0]["dn"], $authPW) ) 
				$retval = true;
		}
		ldap_free_result($ident);
	}
	
	ldap_unbind($conn);
	return $retval;
}

// Search a user by the given filter and returns the attributes defined in the array $required
function ldapSearchUser($filter, $required)
{
	global $AUTHCFG;
	
	$conn = ldapConnectServer();
	if ($conn == NULL)
		return NULL;
	
	$ident = @ldap_search($conn, $AUTHCFG['ldap_basedn'], $filter, $required);
	if ($ident) 
	{
		$result = ldap_get_entries($conn, $ident);
		ldap_free_result($ident);
	}
	ldap_unbind($conn);

	return $result;
}

// Searches for a user's fullname
// returns a hashtable with Account => FullName of all matching users
function ldapSearchUserAccountAndName($user)
{
	global $AUTHCFG;
	
	$fldaccount = strtolower($AUTHCFG['ldap_account']);
	$fldname    = strtolower($AUTHCFG['ldap_fullname']);
	$fldclass   = strtolower($AUTHCFG['ldap_objclass']);

	$usrfilter  = explode("|", $AUTHCFG['ldap_userfilter']);

	$required   = array($fldaccount,$fldname,$fldclass);
	$ldapArray  = ldapSearchUser("$fldname=*$user*", $required);
	
	// copy from LDAP specific array to a standardized hashtable
	// Skip Groups and Organizational Units. Copy only users.
	
	for ($i=0; $i<$ldapArray["count"]; $i++)
	{
		$isuser = false;
		foreach($usrfilter as $filt)
		{
			if (in_array($filt, $ldapArray[$i][$fldclass]))
		    {
		    	$isuser = true;
		    	break;
		    }
		}
		if ($isuser)
		{
			$account = $ldapArray[$i][$fldaccount][0];
			$name    = $ldapArray[$i][$fldname]   [0];
			
			$userArray[$account] = $name;
		}
	}
	return $userArray;
}

// retrieve all requested LDAP values for the given user account
// $fields = array("ldap_forename", "ldap_email",...)
// returns a hashtable with "ldap_forename" => "John"
function ldapGetUserValues($account, $fields)
{
	global $AUTHCFG;
	
	foreach ($fields as $key)
	{
		$required[] = $AUTHCFG[$key];
	}
	
	$filter = $AUTHCFG['ldap_account'] . "=" .$account;
	$ldapArray = ldapSearchUser($filter, $required);
	
	// copy from LDAP specific array to a standardized hashtable
	foreach ($fields as $key)
	{
		$attr  = strtolower($AUTHCFG[$key]);
		$value = $ldapArray[0][$attr][0];
		$valueArray[$key] = $value;
	}
	return $valueArray;
}

function ldapChangePassword($user, $oldPassword, $newPassword)
{
    global $AUTHCFG;
    //In most scenarios, an user entry have to bind itself first so that it can change it's own password.
    //Trying to use an anonymous authentication to find the dn.
    $con = @ldapConnectServer();
    $user_search = ldap_search($con, $AUTHCFG['ldap_basedn'], "(|(uid=$user)(mail=$user))");
    $user_get = ldap_get_entries($con, $user_search);

    $user_entry = ldap_first_entry($con, $user_search);
    $user_dn = ldap_get_dn($con, $user_entry);
    $AUTHCFG['ldap_username'] = $user_dn;
    $AUTHCFG['ldap_pass'] = $oldPassword;

    //Binding again using sepecified user password.
    $con = @ldapConnectServer();

    $user_search = ldap_search($con, $AUTHCFG['ldap_basedn'], "(|(uid=$user)(mail=$user))");
    $user_get = ldap_get_entries($con, $user_search);

    $user_entry = ldap_first_entry($con, $user_search);
    $user_dn = ldap_get_dn($con, $user_entry);

    $entry = array();
    $entry["userPassword"] = "$newPassword";

    return ldap_modify($con,$user_dn,$entry);
}

?>
