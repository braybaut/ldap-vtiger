<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * @Contributor - Elmue 2008
 ************************************************************************************/

// Login Type may be: 'LDAP' or 'AD' or 'SQL'
// Use 'SQL' to login using the passwords stored in the vTiger Sql database
$AUTHCFG['authType']      = 'LDAP';

// ----------- Configuration LDAP -------------
$AUTHCFG['ldap_host']     = '';	//system where ldap is running (e.g. ldap://localhost)
$AUTHCFG['ldap_port']     = '';			//port of the ldap service

// The LDAP branch which stores the User Information
// This branch may have subfolders. PHP will search in all subfolders.
$AUTHCFG['ldap_basedn']   = '';

// The account on the LDAP server which has permissions to read the branch specified in ldap_basedn
$AUTHCFG['ldap_username'] = '';   // set = NULL if not required
$AUTHCFG['ldap_pass']     = ''; // set = NULL if not required

// Predefined LDAP fields (these settings work on Win 2003 Domain Controler)
$AUTHCFG['ldap_objclass']    = 'objectClass';
$AUTHCFG['ldap_account']     = 'uid';
$AUTHCFG['ldap_forename']    = 'givenName';
$AUTHCFG['ldap_lastname']    = 'sn';
$AUTHCFG['ldap_fullname']    = 'cn'; // or "name" or "displayName"
$AUTHCFG['ldap_email']       = 'mail';
$AUTHCFG['ldap_tel_work']    = 'telephoneNumber';
$AUTHCFG['ldap_department']  = 'physicalDeliveryOfficeName';
$AUTHCFG['ldap_description'] = 'description';
$AUTHCFG['sql_accounts'] 	 = array("admin");	//the users whose authentication will be from database instead of from ldap

// Required to search users: the array defined in ldap_objclass must contain at least one of the following values
$AUTHCFG['ldap_userfilter']  = 'user|person|organizationalPerson|account';

//SEAN TSANG PATCH START - Users can change their own password or not.
$AUTHCFG['ldap_allowpasswordchange'] = true;
$AUTHCFG['ldap_admin'] = '';  //Change to NULL if admin do not need to change own password through vTiger
$AUTHCFG['ldap_adminpwd'] = '';  //Change to NULL if admin do not need to change own password through vTiger
//SEAN TSANG PATCH END----------------------------------------------------------

// ------------ Configuration AD (Active Directory) --------------

$AUTHCFG['ad_accountSuffix'] = '';
$AUTHCFG['ad_basedn']        = '';
$AUTHCFG['ad_dc']            = array ( "" ); //array of domain controllers
$AUTHCFG['ad_username']      = NULL; //optional user/pass for searching
$AUTHCFG['ad_pass']          = NULL;
$AUTHCFG['ad_realgroup']     = true; //AD does not return the primary group.  Setting this to false will fudge "Domain Users" and is much faster.  True will resolve the real primary group, but may be resource intensive.

// #########################################################################
?>
