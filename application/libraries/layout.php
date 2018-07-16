<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Layout
{
    // ATTRIBUTS and CONSTRUCT ------------------------------------------------------------------------------------------
    protected $CI;
    protected $layoutName = 'default';
    protected $var = array();

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->var['output'] = '';
        //  Le titre (title) est composé du nom de la méthode et du nom du contrôleur
        //  La fonction ucfirst permet d'ajouter une majuscule
        $this->var['title']   = ucfirst($this->CI->router->fetch_method()) . ' - ' . ucfirst($this->CI->router->fetch_class());
        $this->var['charset'] = $this->CI->config->item('charset');
        $this->var['css']     = array();
	    $this->var['js']      = array();
    }//------------------------------------------------------------------------------------------------------------------

    // METHODS VIEWS ----------------------------------------------------------------------------------------------------
    public function loadView($viewName, $data = array())
    {
        // Cette méthode permet de charger des Vues sans les afficher
        $this->var['output'] .= $this->CI->load->view($viewName, $data, true);
        return $this;
    }
    // Cette méthode permet d'afficher les vues'
    public function showView($viewName, $data = array())
    {
        $this->var['output'] .= $this->CI->load->view($viewName, $data, true);
        $this->CI->load->view('../templates/' . $this->layoutName, $this->var);

    }//------------------------------------------------------------------------------------------------------------------

    // CSS and JS -------------------------------------------------------------------------------------------------------
    public function includeCSS($cssName)
    {
        // Cette méthode permet d'inclure un ou plusieurs fichiers CSS au Layout
        if(is_string($cssName) AND !empty($cssName) AND file_exists('./assets/css/' . $cssName . '.css'))
        {
            $this->var['css'][] = css_url($cssName);
            return true;
        }
        return false;
    }
    // Cette méthode permet d'inclure un ou plusieurs fichiers JS au Layout
    public function includeJS($jsName)
    {
        if(is_string($jsName) AND !empty($jsName) AND file_exists('./assets/js/' . $jsName . '.js'))
        {
            $this->var['js'][] = js_url($jsName);
            return true;
        }
        return false;
    }//------------------------------------------------------------------------------------------------------------------

    // SETTERS ----------------------------------------------------------------------------------------------------------
    public function setTitle($titre)
    {
        // Elle permet de modifier le titre de la page <title> depuis le controleur
        if(is_string($titre) AND !empty($titre))
        {
            $this->var['title'] = $titre;
            return true;
        }
        return false;
    }
    // Elle permet de modifier le charset de la page <meta charset = ""/> depuis le controleur
    public function setCharset($charset)
    {
        if(is_string($charset) AND !empty($charset))
        {
            $this->var['charset'] = $charset;
            return true;
        }
        return false;
    }
    // Elle permet de modifier le Layout à choisir
    public function setLayout($layoutName)
    {
	   if(is_string($layoutName) AND !empty($layoutName) AND file_exists('./application/templates/' . $layoutName . '.php'))
	   {
		   $this->layoutName = $layoutName;
		   return true;
	   }
	   return false;
    }//------------------------------------------------------------------------------------------------------------------
}
