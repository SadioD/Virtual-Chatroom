<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Session extends CI_Session
{
    // Permet de connecter l'USER
    public function setAuthentificated($authentificated = true)
	{
		if(is_bool($authentificated))
		{
		    $_SESSION['auth'] = $authentificated;
		}
	}
    // Permet de vérifier si l'User est connecté
    public function isAuthentificated()
	{
		return isset($_SESSION['auth']) AND $_SESSION['auth'] === true;
	}
}
