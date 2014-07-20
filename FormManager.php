<?php
require_once("Form.php");
require_once("FormUtil.php");

/** @brief Class for managing multiple forms.
 *
 * Each form has a specific key associated with it. Essentially a
 * custom container to help manage multiple forms.
 */
class FormManager{
   // Add multiple forms?
   
   private $form; //!< Array to hold Form(s).
   
   /// Default constructor for class.
   function __construct(){
      $this->form = array();
   }
   
   /// Default de-constructor for class.
   function __destruct(){
      unset($this->form);
   }
   
   /// Add new Form to manager.
   /**
    * Requires unique name (will need to implement check),
    * SQL user, SQL database, and SQL table. 
    * Refer to Form::__construct($datauser, $database, $table).
    */
   public function addNewForm($formName,$dataUser, $dataBase, $table){
      // Implement unique name check!
      $this->form[$formName] = new Form($dataUser, $dataBase, $table);
   }
   
   /// Add existing Form to manager.
   /**
    * Add a Form directly into the manager.
    */
   public function addForm($formName,$form){
      // Implement unique name check!
      $this->form[$formName] = $form;
   }
   
   /// Get the Form with the associated unique ID.
   public function getForm($formName){
      return $this->form[$formName];
   }
   
   /// Set attributes of Form with function as second variable.
   /**
    * $func or given function must only have Form as the parameter.
    */
   public function setFormWFunc($formName,$func){
      $func($this->form[$formName]);
   }
   
   /// Delete Form based on unique name given to FormManger.
   /**
    * This does not currently use Form name variable.
    * May change, however, not very likely. This is because,
    * Form is managed by FormManager, as such there could be unique, short cut, names.
    */
   public function deleteForm($formName){
      unset($this->form[$formName]);
   }
   
   /// Set values of Form given a form name and an array.
   /**
    * Refer to Form::setArrayValue($post)
    */
   public function setValues($formName,$array){
      $this->form[$formName]->setArrayValue($array);
   }
   
   /// Get display of the Form given an unique form identifier.
   /**
    * Refer to Form::getForm()
    */
   public function getDisplay($formName){
      return $this->form[$formName]->getForm();
   }
   
   /// Check if id is used in FormManager, returns true if used.
   public function checkFormId($formName){
      if(isset($this->form[$formName])){
         return true;
      }
      else{
         return false;
      }
   }
   
   /// Retrieve the Form given an unique form identifier.
   /**
    * Form retrieve is based on the associated html names of the Fields in the Form.
    * The function will then set values to the associated Forms.
    * Uses FormUtil::retrieveUsingFormValues($form).
    */
   public function retrieveForm($formName){
      if($this->checkFormId($formName)){
         $form = $this->form[$formName];
      }
      else{
         return false;
      }
      
      return FormUtil::retrieveUsingFormValues($form);
   }
   
   /// Submit the Form given an unique form identifier.
   /**
    * Form submit is based on the associated html names of the Fields in the Form.
    * Uses FormUtil::submitUsingFormValues($form).
    */
   public function submitForm($formName){
      if($this->checkFormId($formName)){
         $form = $this->form[$formName];
      }
      else{
         return false;
      }
      
      return FormUtil::submitUsingFormValues($form);
   }
}
?>