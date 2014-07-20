<?php
require_once("DataBase.php");


/** @brief A static helper class for Form related functions.
 *
 * Supports retrieval of data based on forms. Used by FormManager to 
 * manage the forms.  Currently uses database library.
 */
class FormUtil{

   /// Retrieve values for Form based on the names of the fields and their values.
   /**
    * Uses SQL information set in Form. Must have valid 
    * Database, datauser, table, id, and idvalue set for this
    * function to work properly.
    */
   public static function retrieveUsingFormValues($form){
      $link = DataBase::connectMySQL($form->getDataUser(), $form->getDatabase());
      $result = DataBase::getResult($link, $form->getTable(),$form->getId(), 
         $form->getIdValue());
         
      $field = $form->getFields(); // Will this be memory intensive? Not compared to getArrayValue()
      
      foreach($result as $k=>$v){
         if(isset($field[$k])){
            $field[$k]->setDefault($v);
         }
      }
      return true;
   }


   /// Submit a form based on the names of the fields and their values.
   /**
    * Uses SQL information set in Form. Must have valid 
    * Database, datauser, table, id, and idvalue set for this
    * function to work properly.
    */
   public static function submitUsingFormValues($form){
      $parse = array();
      $var = array();
      $varname = array();
      $count = 0;
      $field = $form->getFields();
      
      if(count($field) == 0){
         // Check if form is empty.
         return false;
      }
      
      foreach($field as $v){
         if(!$v instanceof HtmlText){
         	$parse[$count] = "s";
            $var[$count] = $v->getDefault();
            $varname[$count] = "`".$v->getName()."`";
            $count++;
         }
      }
      
      //Format SQL Query Name
      $rows = "";
      if(count($varname) != 0){
         $rows.= $varname[0]."=?";
      }
      for($x=1; $x<count($varname); $x++){
         $rows.= ",".$varname[$x]."=?";
      }
      
      $link = DataBase::connectMySQL($form->getDataUser(), $form->getDatabase());
      $prep = $link->prepare("UPDATE ".$form->getTable()." SET ".$rows."  
         WHERE ".$form->getId()."='".$form->getIdValue()."'");
      
      for($i = 0; $i < count($var); $i++){
         $bind_name = 'bind'.$varname[$i];
         $$bind_name = $var[$i];
         $param[] = &$$bind_name;
      }
      array_unshift($param, implode($parse));
      call_user_func_array(array($prep,'bind_param'), $param);
      
      $prep->execute();
      if($prep->error){
         $prep->close();
         $link->close();
         return false;
      }
      
      $prep->close();
      $link->close();
      return true;
   }
}



?>