<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Model extends CI_Model
{
    //--------------------------------------------------------------------------------------------------------------//
	/* IMPORTANT : $this->db->result()       => renvoie liste objet => foreach(list as object) echo object->titre	//
	//			   $this->db->result_array() => renvoie liste array => foreach(list as array) echo array['titre']	//
	//			   $this->db->custom_result_object('News') => renvoir liste objet de la classe News (si on l'a créé)//
	//			   $this->db->row()          => renvoie un seul objet => row->titre 								//
	//			   $this->db->row_array()    => renvoie un seul array => row['titre'] */							//
	//--------------------------------------------------------------------------------------------------------------//

    // ADD and UPDATE ----------------------------------------------------------------------------------------------------
    // Cette méthode évite la duplication de code
    public function saveEntry($id = null, $where = null, $escaped_data = array(), $not_escaped_data = array())
    {
        if(isset($id) && !empty($id)) {
            $this->updateEntry($where, $escaped_data, $not_escaped_data);
        }
        else {
            $this->addEntry($escaped_data, $not_escaped_data);
        }
    }
    // Cette méthode ajoute une entrée en BDD
    public function addEntry($escaped_data = array(), $not_escaped_data = array())
    {
        // Cette méthode insère une entrée en BDD
        if(empty($escaped_data) AND empty($not_escaped_data))
        {
            return false;
        }
        return (bool) $this->db->set($escaped_data)
                               ->set($not_escaped_data, null, false)
                               ->insert($this->table);
    }
    // Cette méthode modifie une entrée en BDD
    public function updateEntry($where, $escaped_data = array(), $not_escaped_data = array())
    {
        if(empty($escaped_data) AND empty($not_escaped_data))
        {
            return false;
        }
        if(is_integer($where))
        {
            $where = array('id' => $where);
        }
        return (bool) $this->db->set($escaped_data)
                               ->set($not_escaped_data, null, false)
                               ->where($where)
                               ->update($this->table);
    }//-------------------------------------------------------------------------------------------------------------------
    // DELETE and COUNT --------------------------------------------------------------------------------------------------
    public function deleteEntry($where)
    {
        // Cette méthode supprime une entrée
        if(empty($where))
        {
            return false;
        }
        if(is_integer($where))
        {
            $where = array('id' => $where);
        }
        return (bool) $this->db->where($where)
                               ->delete($this->table);
    }
    // Cette méthode compte des entrées en BDD
    public function countEntries($champ = array(), $valeur = null)
    {
        return (int) $this->db->where($champ, $valeur)
                              ->from ($this->table)
                              ->count_all_results();
    }
    // Cette méthode compte des entrées en BDD + JOIN ON
    public function countJoinedEntries($select, $left_table, $joinOn, $champ = array(), $valeur = null)
    {
        return (int) $this->db->select($select)
                              ->join($left_table, $joinOn)
                              ->where($champ, $valeur)
                              ->from ($this->table)
                              ->count_all_results();
    }

    //---------------------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // GETTERS ARRAYS - STANDARD OBJECTS ----------------------------------------------------------------------------------------------
    // Elle permet de récupérer une lise d'entrées si result = 1 => array(0) - Array d'objet
    public function getData($select = '*', $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null)
    {
        // cette méthode permet de récupérer une liste d'entrées - $where renseigné
        // Ou une seule entrée (getUnique) - $where non enrenseigné => mais résultat array(0)
        return $this->db->select($select)
                        ->from($this->table)
                        ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
                        ->get()
                        ->result();
    }
    // Elle permet de récupérer une lise d'entrées si result = 1 => array(0) - Simple Tableau de variables (Utile pour Ajax)
    public function getArrayforAjax($select = '*', $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null)
    {
        // cette méthode permet de récupérer une liste d'entrées - $where renseigné
        // Ou une seule entrée (getUnique) - $where non enrenseigné => mais résultat array(0)
        return $this->db->select($select)
                        ->from($this->table)
                        ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
                        ->get()
                        ->result_array();
    }

    // Elle permet de récupérer un seule entrée si result = 1 => 1 pas d'array
    public function getUnique($select = '*', $where, $limit = null, $debut = null, $orderId = null, $orderDESC = null)
    {
        // cette méthode permet de récupérer une seule entrée - $where doit etre renseigné sous forme d'array
        return $this->db->select($select)
                        ->from($this->table)
                        ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
                        ->get()
                        ->row();
    }

    // Elle permet de récupérer les donnnées dont le champ est comme $champ (clause LIKE de SQL)
    public function getEntriesLike($champ, $valeur, $limit = null, $debut = null)
	{
		// EXEMPLE SUR UN SYSTEME DE NEWS --------------------------------//
		//  	%$titre  => dont le titre finit par $titre      - before  //
		//		%$titre% => dont le titre est exactement $titre - both    //
		//		$titre%  => dont le titre commence par $titre   - after   //
		return $this->db->select('*')
					    ->from($this->table)
                        ->like($champ, $valeur, 'after')  // SELECT * FROM post WHERE titre LIKE $titre%
                        //->or_like(	 $champ, $valeur, 'before') // SELECT * FROM post WHERE titre LIKE %$titre
					    //->or_like($champ, $valeur, 'both')   // SELECT * FROM post WHERE titre LIKE %$titre%

                        ->limit($limit, $debut)
                        ->get()
					    ->result();
	}
    // Cette méthode permet derécupérer une liste d'entrées de jointures => si result = 1 => array(0)
    public function getJoinData($select = '*', $left_table, $joinOn, $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null)
	{
		/* PROTOTYPE -------------------------------------------------------//
        //  return $this->db->select('*')                                   //
		//			        ->from($this->table)                            //
		//			        ->join('membres', 'membres.id = post.idMembre') //
		//			        ->where('post.id', (int)$id)                    //
		//			        ->get()                                         //
		//			        ->result();                                     //
        //------------------------------------------------------------------*/
		return $this->db->select($select)
					    ->from($this->table)
					    ->join($left_table, $joinOn)
					    ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
					    ->get()
					    ->result();
	}
    // Cette méthode permet derécupérer une seule entrée de jointures => si result = 1 => 1 pas d'array
    public function getJoinUnique($select = '*', $left_table, $joinOn, $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null)
	{
		return $this->db->select($select)
					    ->from($this->table)
					    ->join($left_table, $joinOn)
					    ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
					    ->get()
					    ->row();
	}//-------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // GETTERS ARRAYS - CUSTOMIZED OBJECT ----------------------------------------------------------------------------------------
    // Elle permet de récupérer une lise d'entrées si result = Customized Object
    public function getCustomData($select = '*', $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null, $class)
    {
        // cette méthode permet de récupérer une liste d'objets customizé - $where renseigné
        // Ou un array contenant un objet customisé  - $where non enrenseigné => mais résultat array(0 => object customized)
        return $this->db->select($select)
                        ->from($this->table)
                        ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
                        ->get()
                        ->custom_result_object($class); // Exemple custom_result_object('News')
    }
    // Elle permet de récupérer un seule entrée si result = 1 => 1 objet customizé
    public function getCustomUnique($select = '*', $where, $limit = null, $debut = null, $orderId = null, $orderDESC = null, $class)
    {
        // cette méthode permet de récupérer une seule entrée - $where doit etre renseigné sous forme d'array
        return $this->db->select($select)
                        ->from($this->table)
                        ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
                        ->get()
                        ->custom_row_object($class); // Exemple custom_row_object('News')
    }

    // Requetes de type clause LIKE de SQL => Result = Object customizé
    public function getCustomLike($champ, $valeur, $class)
	{
		// EXEMPLE SUR UN SYSTEME DE NEWS --------------------------------//
		//  	%$titre  => dont le titre finit par $titre      - before  //
		//		%$titre% => dont le titre est exactement $titre - both    //
		//		$titre%  => dont le titre commence par $titre   - after   //
		return $this->db->select('*')
					    ->from($this->table)
					    ->like(	 $champ, $valeur, 'before') // SELECT * FROM post WHERE titre LIKE %$titre
					    ->or_like($champ, $valeur, 'both')   // SELECT * FROM post WHERE titre LIKE %$titre%
					    ->or_like($champ, $valeur, 'after')  // SELECT * FROM post WHERE titre LIKE $titre%
					    ->get()
					    ->custom_result_object($class);
	}
    // Elle permet de récupérer une lise d'entrées jointes si result = Customized Object
    public function getCustomJoinData($select = '*', $left_table, $joinOn, $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null, $class)
	{
		return $this->db->select($select)
					    ->from($this->table)
					    ->join($left_table, $joinOn)
					    ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
					    ->get()
					    ->custom_result_object($class);
	}
    // Elle permet de récupérer un seule entrée jointe si result = 1 => 1 objet customizé
    public function getCustomJoinUnique($select = '*', $left_table, $joinOn, $where = array(), $limit = null, $debut = null, $orderId = null, $orderDESC = null, $class)
	{
		/* PROTOTYPE -------------------------------------------------------//
        //  return $this->db->select('*')                                   //
		//			        ->from($this->table)                            //
		//			        ->join('membres', 'membres.id = post.idMembre') //
		//			        ->where('post.id', (int)$id)                    //
		//			        ->get()                                         //
		//			        ->result();                                     //
        //------------------------------------------------------------------*/
		return $this->db->select($select)
					    ->from($this->table)
					    ->join($left_table, $joinOn)
					    ->where($where)
                        ->limit($limit, $debut)
                        ->order_by($orderId, $orderDESC)
					    ->get()
					    ->custom_row_object(0, $class);
	}//-------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // SQL QUERIES -----------------------------------------------------------------------------------------------------------
    // cette méthode permet de mettre à jour un table WHERE (x=A or x= B) AND (y=A or y=B)
    public function setEntries($colsOne = array(), $whereAnd = array(), $colsTwo = array(), $whereOr = array(), $escapedData = array(), $notEscapedData = array()) {
        if(!empty($whereAnd) && !empty($whereOr)) {
            $sql  = '';
            $sql .= 'UPDATE ' . $this->table . ' SET ';

            // On passe les valeures échapées
            if(!empty($escapedData)) {
                foreach($escapedData as $key => $value) {
                    $sql .= $key . ' = ' . $this->db->escape($value) . ', ';
                }
            }
            // On passe les valeurs non échapées
            if(!empty($notEscapedData)) {
                foreach($notEscapedData as $key => $value) {
                    $sql .= $key . ' = "' . $value . '", ';
                }
            }
            // On retire la dernière virgule pour éviter toute erreur de syntaxe
            $sql  = substr($sql, 0, -2);
            $sql .= ' WHERE';

            return $this->processQuery($sql, $colsOne, $whereAnd, $colsTwo, $whereOr, null, null, false);
        }
        return false;
    }
    // cette méthode permet de recupérer toutes les données WHERE x != x
    public function getAllDataBut($select = '*', $exception = array(), $colsOne = array(), $whereAnd = array(), $colsTwo = array(), $whereOr = array(), $orderBy = array(), $desc = null) {
        if(!empty($exception)) {
            $sql = '';
            $sql .= 'SELECT ' . $select . ' FROM ' . $this->table . ' WHERE ';

            foreach($exception as $key => $value) {
                $sql .= $key . ' != ' . $this->db->escape($value) . ' AND ';
            }
            $sql = substr($sql, 0, -4);

            return $this->processQuery($sql, $colsOne, $whereAnd, $colsTwo, $whereOr, $orderBy, $desc);
        }
        return false;
    }
    // cette méthode permet de recupérer les données les plus récentes (du jour le plus récent)
    public function getLatestData($select = '*', $date, $colsOne = array(), $whereAnd = array(), $colsTwo = array(), $whereOr = array(), $orderBy = array(), $desc = null, $quickProcess = true) {
        if(!empty($date)) {
            $sql = '';
            $sql .= 'SELECT ' . $select . ' FROM ' . $this->table;
            $sql .= ' WHERE ' . $date . ' = (SELECT ' . $date . ' FROM ' . $this->table . ' WHERE (';

            // On passe le reste de la requete
            $sql = $this->applyConditionVars($sql, $colsOne, $whereAnd, $colsTwo, $whereOr);
            $sql .= ') ORDER BY ' . $date . ' DESC LIMIT 1)';
            $sql .= ' AND ';
            return $this->processQuery($sql, $colsOne, $whereAnd, $colsTwo, $whereOr, $orderBy, $desc, $quickProcess);
        }
        return false;
    }
    // Permet de récupérer les données de la date précédant la date entrée (WHERE date = date - 1)
    public function getPreviousData($select = '*', $date, $conversationDate, $colsOne = array(), $whereAnd = array(), $colsTwo = array(), $whereOr = array(), $orderBy = array(), $desc = null, $quickProcess = true) {
        if(!empty($date)) {
            $sql  = '';
            $sql .= 'SELECT ' . $select . ' FROM ' . $this->table;
            $sql .= ' WHERE ' . $date . ' = (SELECT ' . $date . ' FROM ' . $this->table . ' WHERE ' . $date . ' < "' . $conversationDate . '" AND (';

            // On passe le reste de la requete
            $sql = $this->applyConditionVars($sql, $colsOne, $whereAnd, $colsTwo, $whereOr);
            $sql .= ') ORDER BY ' . $date . ' DESC LIMIT 1)';
            $sql .= ' AND ';
            return $this->processQuery($sql, $colsOne, $whereAnd, $colsTwo, $whereOr, $orderBy, $desc, $quickProcess);
        }
        return false;
    }
    // Permet de récupérer les données de la date suivant la date entrée (WHERE date = date + 1)
    public function getNextData($select = '*', $date, $conversationDate, $colsOne = array(), $whereAnd = array(), $colsTwo = array(), $whereOr = array(), $orderBy = array(), $desc = null, $quickProcess = true) {
        if(!empty($date)) {
            $sql  = '';
            $sql .= 'SELECT ' . $select . ' FROM ' . $this->table;
            $sql .= ' WHERE ' . $date . ' = (SELECT ' . $date . ' FROM ' . $this->table . ' WHERE ' . $date . ' > "' . $conversationDate . '" AND (';

            // On passe le reste de la requete
            $sql = $this->applyConditionVars($sql, $colsOne, $whereAnd, $colsTwo, $whereOr);
            $sql .= ') ORDER BY ' . $date . ' LIMIT 1)';
            $sql .= ' AND ';
            return $this->processQuery($sql, $colsOne, $whereAnd, $colsTwo, $whereOr, $orderBy, $desc, $quickProcess);
        }
        return false;
    }//-------------------------------------------------------------------------------------------------------------------
    // Permet de supprimer des entrées (WHERE x = x OR Y = Y)
    public function deleteEntries($colsOne, $whereAnd, $colsTwo, $whereOr) {
        if(!empty($colsOne) && !empty($whereAnd) && !empty($colsTwo) && !empty($whereOr)) {
            $sql  = '';
            $sql .= 'DELETE FROM ' . $this->table . ' WHERE (';

            $sql  = $this->applyConditionVars($sql, $colsOne, $whereAnd, $colsTwo, $whereOr);
            $sql .= ')';
            return $this->db->query($sql);
        }
        return false;
    }//-------------------------------------------------------------------------------------------------------------------
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OTHERS  -----------------------------------------------------------------------------------------------------------
    // cette méthode permet de vérifier si une entrée existe en BDD
    public function isUnique($where, $select = '*') {
        $result =  $this->db->select($select)
                            ->from($this->table)
                            ->where($where)
                            ->get()
                            ->result();

        return !empty($result) ? false : true;
    }//--------------------------------------------------------------------------------------------------------------------------------
    // Permet d'aviter la duplication de code passe les variables x = A or B and y = B or C ---------------------------------------------------------------------------------------------------
    protected function applyConditionVars($sql, $colsOne, $whereAnd, $colsTwo, $whereOr) {
        // On passe les variabes AND X = (A OR B)
        for($i = 0; $i < count($whereAnd); $i++){
            $sql .= $colsOne[$i] . ' = ' . $this->db->escape($whereAnd[$i]) .  ' OR ';
        }
        $sql = substr($sql, 0, -4);

        // On passe les variables AND Y = (A OR B)
        $sql .= ') AND (';
        for($i = 0; $i < count($whereOr); $i++){
            $sql .= $colsTwo[$i] . ' = ' . $this->db->escape($whereOr[$i]) .  ' OR ';
        }
        $sql = substr($sql, 0, -4);
        return $sql;
    }//--------------------------------------------------------------------------------------------------------------------------------
    // Cette méthode traite les requetes SQL (WHERE ORDER BY DESC) pour éviter la duplication  -----------------------------------------------------------------------------------------------------------
    protected function processQuery($sql, $colsOne = array(), $whereAnd = array(), $colsTwo = array(), $whereOr = array(), $orderBy = array(), $desc = null, $quickProcess = true)
    {
        if($quickProcess == true) {
            if(!empty($whereAnd)) {
                for($i = 0; $i < count($whereAnd); $i++){
                    $sql .= $colsOne[$i] . ' = ' . $this->db->escape($whereAnd[$i]);
                }

            }
            if(!empty($whereOr)) {
                for($i = 0; $i < count($whereOr); $i++){
                    $sql .= $colsTwo[$i] . ' = ' . $this->db->escape($whereOr[$i]);
                }
            }
        }
        else {
            $sql .= ' (';

            $sql = $this->applyConditionVars($sql, $colsOne, $whereAnd, $colsTwo, $whereOr);
            $sql .= ')';
        }
        // ORDER BY & DESC
        if(!empty($orderBy)) {
            if(is_array($orderBy)) {
                $sql .= ' ORDER BY ' . $orderBy[0];
                for($i = 1; $i < count($orderBy); $i++) {
                    $sql .= ', ' . $orderBy[$i];
                }
            }
            else {
                $sql .= ' ORDER BY ' . $orderBy;
            }
        }
        if($desc != null) {
            $sql .= ' DESC';
        }
        return $this->db->query($sql);
    }//--------------------------------------------------------------------------------------------------------------------------------
}
