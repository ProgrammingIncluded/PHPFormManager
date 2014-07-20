<?php 
require_once('PHPMailer/PHPMailerAutoload.php');

// Will need to clean and comment code, sorry for who ever reads this.
// I was rushed in programming this...

/** @brief A canvas for form field (components), form info, and display info.
 *
 * Manages each component in an array. Stores attributes to form.
 * Does not contain display info, each individual component does.
 */

class Form{

   private $field; //!< Array to hold fields.
   private $formname; //!< String to hold form name, used for form class. Default to 'form'.
   private $datauser; //!< SQL user name.
   private $database; //!< SQL database name.
   private $table; //!< SQL table name.
   private $id; //!< SQL unique id name, default to 'usr'.
   private $idValue; //!< SQL unique id value, default to 'Master'.
   private $buttonValue; //!< Holds form button value/display text.
   private $buttonName; //!< Holds form button class name.
   
   /// Constructor for Form class.
   /**
    * Requires a user, database, and table for SQL use in Form.
    * This decision was made as forms must have some sort of association with SQL.
    * May be changed if not heavily used.
    */
   public function __construct($datauser, $database, $table){
      $this->field = array();
      $this->datauser = $datauser;
      $this->database = $database;
      $this->formname = "form";
      $this->table = $table;
      $this->buttonValue = "Submit";
      $this->buttonName = "submit";
      // Variable used for any users identification
      $this->idValue = "Master";
      $this->id = "usr";
   }
   
   // Deconstructor for Form.
   /**
    * Unsets stored fields.
    */
   public function __destruct(){
      unset($this->field);
   }
   
   /// Add a new field to the From.
   /**
    * The html name of the field must be unique.
    * Name is used for an unqiue key in form.
    */
   public function addField($fobject){
      $this->field[$fobject->getName()] = $fobject;
   }
   
   /// Gets an array of Fields within Form.
   /**
    * Returns an array with key as Fields name and value as Fields class.
    */
   public function getFields(){
      return $this->field;
   }
   
   /// May be deprecated: Adds HTML to form, uses html field.
   /** 
    * Adds HTML directly into form without creating extra components.
    * Used to be used for the early days of Alpha. Now is unnecessary due to
    * component design decisions.
    */
   public function addHTMLText($text){
      $htmlText = new HtmlText($text);
      $this->field[count($this->field)] = $htmlText;
   }
   
   /// Set display button string value/text.
   /** 
    * Shows the text in the button. Only the display text.
    * This does not affect the html name of the button. Refer to Form::setButtonName($name).
    */
   public function setButtonValue($button){
      $this->buttonValue = $button;
   }
   
   /// Get value/display text of the button
   /**
    * Note: Not name attribute in html. It is the text
    * displayed in the button. Refer to Form::getButtonValue().
    */
   public function getButtonValue(){
      return $this->buttonValue;
   }
   
   /// Set button value for html. 
   /** 
    * Does not set the display text of the button. Modifies the name of button
    * in html. Use this for css attributes.
    */
   public function setButtonName($name){
      $this->buttonName = $name;
   }
   
   /// Get name of the button.
   /** 
    * Note: Not the display text, but the name in html. 
    */
   public function getButtonName(){
      return $this->buttonName;
   }
   
   /// Get the field's default value stored in form based on the name of the component.
   /**
    * The name of the form is created by the constructor of the component.
    * Refer to: Fields::__construct().
    */
   public function getValue($nm){
   if($this->field[$nm] instanceof DateList)
   {
      return $this->field[$nm]->getDefault();
   }else{
      return $this->field[$nm]->getDefault();
      }
   }
   
   /// Set default value for a field in form.
   /**
    * Name is the html name of the field.
    * Value must be the format of the field or else errors will occur by PHP.
    * The function does not automatically catch wrong settings. May add later in the line.
    * Bad coding, I know...
    */
   public function setValue($nm,$val){
      $this->field[$nm]->setDefault($val);
   }
   
   /// Set default value for field in form based on array.
   /**
    * For single values, use Fields::setValue($nm, $val). Array should have
    * name of field as key and default value for field as value in array.
    */
   public function setArrayValue($post){
      $setValue;
      foreach($this->field as $v){
         $name = str_replace(' ', '_', $v->getName());
         
         if($v instanceof DateList && !isset($post[$name])){
         	$datestr="";
         	$datestr = $post[$name."Month"]." ".$post[$name."Day"]." ".$post[$name."Year"];
         	$dt = date('m/d/Y',strtotime($datestr));
         	$setValue = $dt;
         }
         else if($v instanceof TimeList && !isset($post[$name])){
         	$timestr="";
         	$timestr = $post[$name."min"]." ".$post[$name."sec"];
         	$setValue = $timestr;
         }
         else if(!$v instanceof HtmlText){
            $setValue = $post[$name];
         }
         else{
            continue;
         }
         
         if($v->getOverideDef() != NULL){
            $overide = $v->getOverideDef();
            $setValue = $overide($setValue, $this->field);
         }
            $v->setDefault($setValue);
      }
   }
   
   /// Get all the field's default value in form as an array.
   /**
    * The key for the returned array is the unique html name from the fields.
    * All values, except HtmlText is returned.
    */
   public function getArrayValue(){
      $result = array();
      
      foreach($this->field as $k=>$v){
         if(!$v instanceof HtmlText){
            $name = str_replace(' ', '_', $v->getName());
            
            if($v->getOverideDef() != NULL){
               $func = $v->getOverideDef();
               $value = $func($v->getDefault(), $this->field);
               $result[$name] = $value;
            }
            else{
               $result[$name] = $v->getDefault();
            }
         }
      }

      return $result;
   }
   
   /// Set SQL database name for Form.
   public function setDatabase($database){
      $this->database = $database;
   }

   /// Get SQL database name for Form.
   public function getDatabase(){
      return $this->database;
   }

   /// Set SQL user for Form.
   public function setDataUser($datauser){
      $this->datauser = $datauser;
   }
   
   /// Get SQL user for Form.
   public function getDataUser(){
      return $this->datauser;
   }
   
   /// Set SQL table for Form.
   public function setTable($table){
      $this->table = $table;
   }
   
   /// Get SQL table for Form.
   public function getTable(){
      return $this->table;
   }
   
   /// Set SQL Id value for Form.
   /**
    * Used for the unique id variable value for SQL.
    * Not the name of the variable in SQL. Refer to Form::setId($id) for name.
    */
   public function setIdValue($idValue){
      $this->idValue = $idValue;
   }
   
   /// Get SQL Id variable value.
   /**
    * Gets the unique id variable value for SQL.
    * Not the name of the variable in SQL. Refer to Form::getID() for name.
    */
   public function getIdValue(){
      return $this->idValue;
   }
   
   /// Set SQL Id variable name.
   /**
    * Sets the unique id variable name for SQL.
    * Not the value of the variable in SQL. Refer to Form::setIdValue($idValue) for value.
    */
   public function setId($id){
      $this->id = $id;
   }
   
   /// Get SQL Id variable name.
   /**
    * Gets the unique id variable name for SQL.
    * Not the value of the variable in SQL. Refer to Form::getIdValue() for value.
    */
   public function getId(){
      return $this->id;
   }
   
   /// Delete a field in form.
   /**
    * Return true if field deleted successfully.
    * False if field does not exist with given name.
    */
   public function deleteField($nm){
      if(isset($this->field[$nm])){
         unset($this->field[$nm]);
         return true;
      }
      else{
         return false;
      }
   }
   
   /// Get display contents for form.
   /**
    * Only returns display contents, does not display.
    * Client must echo out the content returned.
    */
   public function getForm(){
      $display = "";
      $display .= '<form class="'.$this->formname.'"action="" method="post">';
      
      foreach($this->field as $v){
         $display .= $v->getContents();
      }
      
      $display .= '
                 <input type="submit" name="'.$this->buttonName.'" value="'.$this->buttonValue.'" class="button" />
                 </form>';
      return $display;
   }
      // MIGHT BE DEPRECATED OR WILL NEED TO BE TIDIED
   /* 
   public function getFinalization(){
      
      if(count($this->field)!=0){
         $display = "";
         $display .= '<form action="" method="post">';
         foreach($this->field as $v){
            if($v instanceof DateList){
               $display .= "".$v->getShownName().": ".$v->getDefault();
               $display .= '<input type="hidden" name="'.$v->getName().'" value="'. $v->getDefault().'"/> ';
               $display.="<br />";
            }
            else if(!$v instanceof HtmlText && $v->getFinalization() == true){
               $display .= "".$v->getShownName().": ".$v->getDefault();
               $display .= '<input type="hidden" name="'.$v->getName().'" value="'. $v->getDefault().'"/> ';
               $display.="<br />";
            }
         }
         
         	$display .= ' <br />
            <input type="submit" name="edit" value="Edit" class="button" />
            </form>';

         unset($v);
         return $display;
      }else{
         echo "Sorry, error finalizing form.";
         return 0;
      }
   }
   
   public function setSubmitted($var,$lim){
		$x=0;
		$keys = array_keys($this->field);
		
		foreach($var as $v){
			if($x < $lim){
				$this->field[$keys[$x]]->setDefault($v);
				$x++;
			}
		}
   }
   */
   
   // These functions are too specific, change to generalization, let client parse info.
   /* 
   public function sendUpdateEmail($username){
      $link = $this->connectMySQL("cmtanc_database", "cmtanc_user");
      $prep = $link->prepare("SELECT email FROM cmtanc_members WHERE usr=?");
      $prep->bind_param("s",$username);
      $prep->execute();
      $prep->bind_result($prepemail);
       if($prep->fetch()){
         $email = $prepemail;
      }
      $prep->close(); 
      
      $subject = $this->formname." Update";
      $body = "Hi ".$username.",\n\nThere has been a recent change to your online application. Here is the list of the updated information.\n\n".$this->getInfoForEmail()."\n\nIf you believe there has been an error, please login to your account and edit your information. We thank you for your cooperation.\n\nFrom,\nCMTANC\nInternational Youth Music Competition\nP.O. Box 3382 Saratoga, CA 95070 USA";
      
      $this->send_mail("CMTANC_INFO", "info@cmtanc.org", $email, $subject, $body);
      $this->send_mail("CMTANC_INFO", "info@cmtanc.org", "competition@cmtanc.org", $subject, $body);
   }
   
   public function sendSubmitEmail($username){
      $link = $this->connectMySQL("cmtanc_database", "cmtanc_user");
      $prep = $link->prepare("SELECT email FROM cmtanc_members WHERE usr=?");
      $prep->bind_param("s",$username);
      $prep->execute();
      $prep->bind_result($prepemail);
       if($prep->fetch()){
         $email = $prepemail;
      }
      $prep->close(); 
      
      $subject = $this->formname." Submit";
      $body = "Hi ".$username.",\n\nYou have successfully submitted the ".$this->formname.". Here is the list of the submitted information.\n\n".$this->getInfoForEmail()."\n\nIf you believe there has been an error, please login to your account and edit your information. We thank you for your cooperation.\n\nFrom,\nCMTANC\nInternational Youth Music Competition\nP.O. Box 3382 Saratoga, CA 95070 USA";
      $body.= "\n\nCheck List:\n\n□ Application form, the E-Mail with the submitted information. □ Proof of age (photocopy of either driver’s license, passport or birth certificate) □ Preliminary CD (DO NOT write contestant’s name!) □ Application Fee: (non-refundable) $60.00 (Students of Active CMTANC members); $90.00 (Students of Non-CMTANC members); $120.00 (International contestants please pay application fee with money order or cashier check payable to CMTANC in US dollar). ** Vocal Ensemble will be deferment.";
      
      $this->send_mail("CMTANC_COMPETITION", "competition@cmtanc.org", $email, $subject, $body);
      $this->send_mail("CMTANC_COMPETITION", "competition@cmtanc.org", "competition@cmtanc.org", $subject, $body);
   }
   
   private function getInfoForEmail(){
      $display = "";
      
      foreach($this->field as $v){
            if($v->getFinalization()&&$v instanceof DateList){
               $display .= "".$v->getShownName().": ".$v->getDefault();
               $display .= "\n";
            }
            else if($v->getFinalization() && !$v instanceof HtmlText ){
               $display .= "".$v->getShownName().": ".$v->getDefault();
               $display .= "\n";
            }
         }
      return $display;
   }
   */  
   
   /// Set the form name
   /** 
    * Sets the name of the form in html. When access forms or css, use the value set here.
    * Default set to 'form'.
    */
   public function setFormName($nm){
      $this->formname = $nm;
   }
   
   // Will be deprecated: Custom email function.
   /* 
    * Used for making Form not dependent on helper functions. Makes class independent.
    * This function was used in the now deprecated email form function in form.
    * Will move this into another static class as a helper/util class.
    */
    /*
   private function send_mail($name,$from,$to,$subject,$body)
   {
      $name = strtoupper($name);
      $mail = new PHPMailer;
      $mail->From = $from;
      $mail->FromName = $name;
      $mail->addAddress($to);
      $mail->Subject = $subject;
      $mail->WordWrap = 80;
      $mail->addAttachment("2014_competition_checklist.pdf", "2014 Competition Checklist");
      $mail->Body = $body;
      $mail->send();
   }
   */
}

/** @brief Abstract class for fields.
 *
 * Has basic field properties. All fields that are used in form must
 * extend this class.
 */
abstract class Fields{
      
   protected $data;
   protected $def;
   protected $name;
   protected $shownName;
   protected $displayName;
   protected $hasBreak;
   protected $showFinalization;
   protected $isCapital;
   protected $classVal;
   protected $divClassVal;
   protected $overideFunc;
   protected $hasDiv;
   
   /// Default constructor for field.
   /**
    * Requires a name for the field. Name used for both display and html names.
    * Display name can then be over written by Fields::setShownName($nm).
    */
   public function __construct($nm){
      $this->data = array();
      $this->def = "";
      $this->name = $nm;
      $this->shownName = $nm;
      $this->displayName = 1;
      $this->hasBreak = true;
      $this->hasDiv = true;
      $this->isCapital = true;
      $this->showFinalization = true;
      $this->classVal = "";
      $this->divClassVal = "";
      $overideFunc = NULL;
   }
   
   /// Default de-constructor for class. 
   public function __destruct(){
      unset($this->data);
      unset($this->name);
      unset($this->def);
      unset($this->displayName);
      unset($this->floatLeft);
   }
   
   /// Get the value of the field as html format. Used by form. Must be overridden.
   abstract public function getContents();

   /// Get if there should be a break after the field.
   public function getHasBreak(){
      return $this->hasBreak;
   }
   
   /// Set if there should be a break after the field.
   public function setHasBreak($nm){
      $this->hasBreak = $nm;
   }
   
   /// Set label value, or displayed name before the field.
   /**
    * Does not affect the html attribute name of the field. Only changes the name
    * displayed in web page. Fields::getWillDisplayName() must be 
    * true in order for name to appear.
    */
   public function setShownName($nm){
      $this->shownName = $nm;
   }
   
   /// Get label value, or displayed name before the field.
   /**
    * Does not affect the html attribute name of the field. Only changes the name
    * displayed in web page. Fields::getWillDisplayName() must be 
    * true in order for name to appear.
    */
   public function getShownName(){
      return $this->shownName;
   }
   
   /// Get if field is wrapped in div.
   public function getHasDiv(){
      return $this->hasDiv;
   }
   
   /// Set if field is wrapped in div.
   public function setHasDiv($nm){
      $this->hasDiv = $nm;
   }
   
   /// Give a function with string return for override default Fields::getContents().
   /**
    * The given function will run and edit the value returned in Form::getArrayValue() instead of just the usual 
    * Fields::getContents() when Form parses the this field.
    * Please note that the function must have the following
    * parameters: one parameter for default value of field (dependent on field) and
    * one parameter for an array of fields (the Form::getArrayValue() arrays 
    * except with HtmlText fields). Function must also return a string value to overide 
    * the value of the field.
    * Please note that this is a very dangerous function use with caution.
    *  Mainly used for custom values in locked input fields.
    */
   public function setOverideDef($overide){
      $this->overideFunc = $overide;
   }
   
   /// Returns an override function if it exists. Else returns empty.
   public function getOverideDef(){
      return $this->overideFunc;
   }
   
   /// Set html value of the field.
   public function setDefault($d){
      $this->def = $d;
   }
   
   /// Get html value of the field.
   public function getDefault(){
      $return = $this->def;
      if($this->isCapital){
         $return = ucfirst($return);
      }
      return $return;
   }
   
   /// Get unique html name of the field.
   public function getName(){
      return $this->name;
   }
   
   /// Set custom field class.
   /**
    * Sets custom field's html class only. Not the field's div class.
    * Refer to Fields::setDivClass($nm)
    */
   public function setClass($nm){
      $this->classVal = $nm;
   }
   
   /// Get custom field class.
   /**
    * Gets custom field's html class only. Not the field's div class.
    * Refer to Fields::getDivClass()
    */
   public function getClass(){
      return $this->classVal;
   }
   
   /// Set custom div classes, can be multiple.
   /**
    * Show div must be true for this function to work properly.
    * Check with Fields::getHasDiv() and use Fields::setHasDiv($nm) to set.
    * Does not refer to field's html class only it's div. Refer to Field::setClass($nm).
    */
   public function setDivClass($nm){
      $this->divClassVal = $nm;
   }
   
   /// Get custom div classes for field.
   public function getDivClass(){
      return $this->divClassVal;
   }
   
   /// May be deprecated: Used for specific task from previous client.
   public function setFinalization($dn){
      $this->showFinalization = $dn;
   }
   
   /// May be deprecated: Used for specific task from previous client.
   public function getFinalization(){
      return $this->showFinalization;
   }
   
   /// Set whether or not a label will appear before the actual field.
   public function setWillDisplayName($dn){
      if($dn>=1){
         $this->displayName = 1;
      }
      else if($dn<=0){
         $this->displayName = 0;
      } 
   }
   
   /// Get whether or not a label will appear before the actual field.
   public function getWillDisplayName(){
      return $this->displayName;
   }
   
   /// Set whether or not to capitalize for the letter of the field.
   public function setIsCapital($bool){
      $this->isCapital = $bool;
   }
   
   /// Get whether or not to capitalize for the first letter of the field.
   public function getIsCapital(){
      return $this->isCapital;
   }

}

/** @brief Drop list field for Form
 * Used for drop down lists within html form.
 */
class DropList extends Fields{

   /// Add options to DropList field.
   /**
    * $key is html name of the option and $value is the physical
    * value data of the option.
    */
   public function addOption($key,$value){
      $this->data[$key] = $value;
   }
   
   /// Delete an option based on it's key (html name) not value.
   public function deleteOption($key){
      if(isset($this->data[$key])){
         unset($this->data[$key]);
         return true;
      }
      else{
      return false;
      }
      
   }

   /// Get the value of the field as html format. Used by form.
   public function getContents(){
      $display = "";
      if($this->getHasDiv()){
        $display .='<div class="'.$this->divClassVal.'">';
      }
      if($this->getWillDisplayName()==1){
         $display .= '<label class="'.$this->classVal.'" id="'.$this->name.'" for="'.$this->name.'">'.$this->shownName.':</label>';
      }
      $display .= '<select name="'.$this->name.'" class="'.$this->classVal.'">';
      
      if(count($this->data)==0){return 0;}
         foreach($this->data as $key=>$value){
            if($key == $this->getDefault() || $value == $this->getDefault()){
               $display .= "<option value =\"".$key."\" selected=\"selected\">".$value."</option>";
            }else{
               $display .= "<option value =\"".$key."\">".$value."</option>";
            }
         }
      $display .="</select>";
      if($this->getHasDiv()){
         $display .= "</div>";
      }
      if($this->getHasBreak()){
         $display .='<br class="'.$this->divClassVal.'"/>';
      }
      unset($key);
      unset($value);
      return $display;
   }
   
   public function getDefault(){
      return $this->data[$this->def];
   }

   public function setDefault($option){
      foreach($this->data as $k => $v){
          if($v == $option || $k == $option){
            $this->def = $k;
            unset($k);
            unset($v);
            return true;
          }
      }
      $this->def = "default";
      return false;
   }
   
}

/** @brief A special drop list for dates to be used in Form.
 *
 * Class manages three drop lists: month, date, and year.
 * The class acts accordingly and reacts like a single field.
 */
class DateList extends Fields{
   
   /// Constructor for class. 
   /**
    *Requires unique name to be used in field, html name attribute, and Forms.
    */
   public function __construct($nm){
      $this->data = array();
      $this->def = new DateTime('01/01/'.date('Y'));
      $this->name = $nm;
      $this->shownName = $nm;
      $this->hasDiv = true;
      $this->displayName = 1;
      $this->divClassVal = "";
      $this->hasBreak =true;
      $this->classVal = "";
      $this->formatDList($nm);
      $this->showFinalization = true;
   }
   
   /// Default de-constructor for class.
   public function __destruct(){
      unset($this->data);
      unset($this->name);
      unset($this->def);
      unset($this->displayName);
      unset($this->floatLeft);
   }
     
   public function setClass($nm){
      $this->classVal = $nm;
      foreach($this->data as $v){
         $v->setClass($this->classVal);
      }
   }
      
   public function setDivClass($nm){
      $this->divClassVal = $nm;
      foreach($this->data as $v){
         $v->setDivClass($this->divClassVal);
      }
   }
   
   public function getDate(){
   	return $this->def;
   }
   
   public function getDefault(){
      return $this->def->format('m/d/Y');
   }
   
   /// Set default for class value
   /**
    * Default must be in format of 'm/d/Y' with month and day being two digits. 
    * Year being four. Slashes can also be  '-' as they will be 
    * replaced automatically into '/'.
    */
   public function setDefault($d){
      if($d==""){
         $d= "1/1/2013";
      }
      $d = str_replace('-', '/', $d);
      $this->def = new DateTime($d);
      if(!checkdate($this->def->format('m'),$this->def->format('d') ,$this->def->format('Y'))){
         $this->def = new DateTime('01/01/'.Date('Y'));
      }
      $this->data["month"]->setDefault($this->def->format('F'));
      $this->data["day"]->setDefault($this->def->format('d'));
      $this->data["year"]->setDefault($this->def->format('Y'));
   }
   
   /// Get the value of the field as html format. Used by form.
   /** 
    * Default html names for the three drop lists are $this->name.'month'
    * $this->name.'day' and $this->name.'year'.
    */
   public function getContents(){
      if($this->getHasDiv()){
         $display = '<div class="'.$this->divClassVal.'">';
      }
      if($this->getWillDisplayName() == 1){
            $display .= "<label class=\"".$this->classVal." \" name=\"".$this->name."\" for=\"".$this->name."\">".$this->shownName.":</label>";
         }

      foreach($this->data as $v){
            $display .= $v->getContents(); 
      }
      if($this->getHasDiv()){
         $display.="</div>";
      }
      if($this->getHasBreak()){
         $display .='<br class="'.$this->divClassVal.'"/>';
      }
      unset($v);
      return $display;
   }
   
   /// Private function to format and set up the three individual drop lists.
   private function formatDList($nm){ 
      $month = new dropList($nm."Month");
      $day = new dropList($nm."Day");
      $year = new dropList($nm."Year");
      $month -> setWillDisplayName(0);
      $day -> setWillDisplayName(0);
      $year -> setWillDisplayName(0);
      $month->setHasDiv(false);
      $day->setHasDiv(false);
      $year->setHasDiv(false);
      $month->setHasBreak(false);
      $day->setHasBreak(false);
      $year->setHasBreak(false);
      
      
      $month -> addOption("jan","January");
      $month -> addOption("feb","February");
      $month -> addOption("mar","March");
      $month -> addOption("apr","April");
      $month -> addOption("may","May");
      $month -> addOption("jun","June");
      $month -> addOption("jul","July");
      $month -> addOption("aug","August");
      $month -> addOption("sep","September");
      $month -> addOption("oct","October");
      $month -> addOption("nov","November");
      $month -> addOption("dec","December");
      $month -> setDefault($this->def->format('F'));
      
      for($x=0; $x <=9; $x++){
         $day->addOption("0".$x,"0".$x);
      }
      for($x=10;$x<=31;$x++){
         $day-> addOption((string)$x,(string)$x);
      }
      $day-> setDefault($this->def->format('d'));
      
      for($x=date('Y')-1;$x >= date('Y')-60;$x--){
         $year->addOption((string)$x,(string)$x);
      }
      $year->setDefault($this->def->format('Y'));
      
      $this->data = array(
         "month" => $month,
         "day" => $day,
         "year" => $year,
      );
   }
}

/** @brief A special drop list for time to be used in Form.
 *
 * Supports up to an hour of time. If more, normal lists should be used.
 * This was used for special cases where formatting of time is of an issue.
 * Much like DateList where multiple DropList are created and managed.
 * 
 */
class TimeList extends Fields{
   /// Constructor for class. 
   /**
    *Requires unique name to be used in field, html name attribute, and Forms.
    */
   public function __construct($nm){
      $this->data = array();
      $this->def = "00m 00s";
      $this->name = $nm;
      $this->shownName = $nm;
      $this->displayName = 1;
      $this->hasDiv = true;
      $this->divClassVal="";
      $this->hasBreak =true;
      $this->classVal = "";
      $this->formatList($nm);
      $this->showFinalization = true;
   }
   
   /// Default de-constructor for class.
   public function __destruct(){
      unset($this->data);
      unset($this->name);
      unset($this->def);
      unset($this->displayName);
      unset($this->floatLeft);
   }
   
   /// Set default of TimeList.
   /**
    * Must be formatted as '00m 00s' with a space.
    */
   public function setDefault($d){
      if($d==""){
         $d= "00m 00s";
      }
      $numbers = array();
      $numbers = explode(" ", $d);
      $this->def = $d;
      if(!isset($numbers[0]) && !isset($numbers[1])){
         $numbers[0] = "00";
         $number[1] = "00";
      }
      $this->data["min"]->setDefault($numbers[0]);
      $this->data["sec"]->setDefault($numbers[1]);
   }
   
   /// Get the value of the field as html format. Used by form.
   /**
    * Individual DropList html names are: $this->name.'sec' and $this->name.'min'.
    */
   public function getContents(){
      $display ="";
      if($this->getHasDiv()){
         $display .= '<div class="'.$this->divClassVal.'">';
      }
      if($this->getWillDisplayName() == 1){
            $display .= "<label class=\"".$this->classVal."\" name=\"".$this->name."\" for=\"".$this->name."\">".$this->shownName.":</label>";
      }
      $count =0;
      foreach($this->data as $v){
            $display .= $v->getContents();
            $count++;
      }
      if($this->getHasDiv()){
         $display.="</div>";
      }
      if($this->getHasBreak()){
         $display .='<br class="'.$this->divClassVal.'"/>';
      }
      unset($count);
      unset($v);
      return $display;
   }
   
   public function setClass($nm){
      $this->classVal = $nm;
      foreach($this->data as $v){
         $v->setClass($this->classVal);
      }
   }
   
   public function setDivClass($nm){
      $this->divClassVal = $nm;
      foreach($this->data as $v){
         $v->setDivClass($this->divClassVal);
      }
   }
   
   private function formatList($nm){ 
      $min = new dropList($nm."min");
      $sec = new dropList($nm."sec");
      $min -> setWillDisplayName(0);
      $sec -> setWillDisplayName(0);
      $min->setHasBreak(false);
      $sec->setHasBreak(false);
      $min->setHasDiv(false);
      $sec->setHasDiv(false);
      
      for($x=0; $x <=9; $x++){
         $min->addOption("0".$x."m","0".$x."m");
         $sec->addOption("0".$x."s","0".$x."s");
      }
      
      for($x=10;$x<=59;$x++){
         $min-> addOption((string)$x."m",(string)$x."m");
         $sec-> addOption((string)$x."s",(string)$x."s");
      }
      $min-> setDefault("00m");
      $sec-> setDefault("00s");
      
      $this->data = array(
         "min" => $min,
         "sec" => $sec
      );
   }
}

/** @brief Text input bar field for Form.
 *
 * The most used class for field.
 * It is simple in code as it only manages one data value.
 * Refer to TextBox for words longer than 5. (Note: May be exceptions)
 * 
 */
class TextInput extends Fields{
      
      private $size = 10;
      private $disabled;

   /// Get the value of the field as html format. Used by form.
   public function getContents(){
      $display ="";
      if($this->getHasDiv()){
         $display .='<div class="'.$this->divClassVal.'">';
      }

      if($this->getWillDisplayName()==1){
         $display .= "<label class=\"".$this->classVal."\" name=\"".$this->name."\" for=\"".$this->name."\">".$this->shownName.":</label>";
      }
      
      $display .= "<input class=\"".$this->classVal."\" type=\"text\" name=\"".$this->name.
      "\" size=\"".$this->size."\" value=\"".$this->def."\" ".$this->disabled.">";
      
      if($this->getHasDiv()){
         $display .= "</div>";
      }
      
      if($this->getHasBreak()){
         $display .='<br class="'.$this->divClassVal.'"/>';
      }
      
      return $display;
   }
   
   /// Set size or length of the input bar. Note: Height is not affected.
   public function setSize($s){
      $this->size = $s;
   }
   
   /// Set if the input bar is locked.
   /**
    * Please note that when the input is locked, $_POST will not contain this variables.
    * This means that Form will not being able to read this info when passing $_POST
    * directly to Form::setArrayValue($post).
    * As such, if you want to modify the value displayed in lock form, 
    * Fields::setOverideDef($overide) may be used.
    */
   public function setDisabled($d){
   	  if($d == 0){
   			$this->disabled = " ";
      }else{
      		$this->disabled = "disabled";
      }
   }
     
   /// Get boolean on if the input bar is locked.
   public function getDisabled(){
   		return $this->disabled;
   }
}

/** @brief Text box field for Form.
 *
 * Very much like TextInput, as such, extends from it.
 * Will need to add a height adjustment variables.
 */

class TextBox extends TextInput{
   /// Constructor for class. 
   /**
    *Requires unique name to be used in field, html name attribute, and Forms.
    */
   public function __construct($nm){
      $this->data = array();
      $this->def = "Enter Student Names Here. Use Comma to Separate Names";
      $this->name = $nm;
      $this->shownName = $nm;
      $this->displayName = 1;
      $this->hasBreak = true;
      $this->size = 10;
      $this->hasDiv = true;
      $this->isCapital = true;
      $this->showFinalization = true;
      $this->classVal = "";
      $this->divClassVal = "";
      $overideFunc = NULL;
   }
   
   /// Get the value of the field as html format. Used by form.
   public function getContents(){
      $display ="";
      if($this->getHasDiv()){
         $display .='<div class="'.$this->divClassVal.'">';
      }

      if($this->getWillDisplayName()==1){
         $display .= "<label class=\"".$this->classVal."\" name=\"".$this->name."\" for=\"".$this->name."\">".$this->shownName.":</label>";
      }
      
      $display .= "<textarea class=\"".$this->classVal."\" name=\"".$this->name.
      "\" cols =\"30\" rows=\"".$this->size."\" ".$this->disabled.">".$this->def."</textarea>";
      
      if($this->getHasDiv()){
         $display .= "</div>";
      }
      
      if($this->getHasBreak()){
         $display .='<br class="'.$this->divClassVal.'"/>';
      }
      
      return $display;
   }
}

/** @brief Contains html text to output in Form.
 *
 * Raw html can be stored and will out put accordingly.
 * A very simple, yet elegant class.
 * The text will output based on the order in which this field
 * is added to the form. Only class that Form will 
 * skip when returning array values. As such, HtmlText will not
 * be saved into SQL. Essentially, will be non-existent
 * to the form class. If using this for formatting or css, refer to custom
 * div settings for individual fields prior to using this HtmlText.
 */
class HtmlText extends Fields{
   /// Constructor for class. 
   /**
    * Requires unique name to be used in Forms. 
    * Nothing to do with fields or html attributes. There are none!
    */    
   public function __construct($nm){
      $this->data = array();
      $this->def = $nm;
      $this->hasDiv = true;
      $this->shownName = "";
      $this->name = "";
      $this->classVal;
      $this->divClassVal;
      $this->displayName = 1;
      $this->floatLeft = 0;
   }
   
   /// Default de-constructor for class.
   public function __destruct(){
      unset($this->data);
      unset($this->name);
      unset($this->def);
      unset($this->displayName);
      unset($this->floatLeft);
   }
   
   /// Get the value of the field as html format. Used by form.
   public function getContents(){
      return $this->def;
   }
}

/** @brief A special drop list for countries.
 *
 * Essentially a pre-defined drop list with preloaded countries list
 * hard coded into the script.
 * Please note that it extends DropList, as such, options can be added or removed.
 * Also, Countries are in alphabetical order except for U.S. where it is first. (May be deprecated)
 * Bug: Set default does not seem to work as of Beta Ver. 1.4
 */
class CountryList extends DropList{
   /// Constructor for class. 
   /**
    *Requires unique name to be used in field, html name attribute, and Forms.
    */
    public function __construct($nm){
      $this->data = array();
      $this->def = "United States";
      $this->name = $nm;
      $this->shownName = $nm;
      $this->displayName = 1;
      $this->hasBreak= true;
      $this->hasDiv = true;
   
     $this->addOption("United States","United States");
     $this->addOption("Afghanistan","Afghanistan");
     $this->addOption("Åland Islands","Åland Islands");
     $this->addOption("Albania","Albania");
     $this->addOption("Algeria","Algeria");
     $this->addOption("American Samoa","American Samoa");
     $this->addOption("Andorra","Andorra");
     $this->addOption("Angola","Angola");
     $this->addOption("Anguilla","Anguilla");
     $this->addOption("Antarctica","Antarctica");
     $this->addOption("Antigua and Barbuda","Antigua and Barbuda");
     $this->addOption("Argentina","Argentina");
     $this->addOption("Armenia","Armenia");
     $this->addOption("Aruba","Aruba");
     $this->addOption("Australia","Australia");
     $this->addOption("Austria","Austria");
     $this->addOption("Azerbaijan","Azerbaijan");
     $this->addOption("Bahamas","Bahamas");
     $this->addOption("Bahrain","Bahrain");
     $this->addOption("Bangladesh","Bangladesh");
     $this->addOption("Barbados","Barbados");
     $this->addOption("Belarus","Belarus");
     $this->addOption("Belgium","Belgium");
     $this->addOption("Belize","Belize");
     $this->addOption("Benin","Benin");
     $this->addOption("Bermuda","Bermuda");
     $this->addOption("Bhutan","Bhutan");
     $this->addOption("Bolivia","Bolivia");
     $this->addOption("Bosnia and Herzegovina","Bosnia and Herzegovina");
     $this->addOption("Botswana","Botswana");
     $this->addOption("Bouvet Island","Bouvet Island");
     $this->addOption("Brazil","Brazil");
     $this->addOption("British Indian Ocean Territory","British Indian Ocean Territory");
     $this->addOption("Brunei Darussalam","Brunei Darussalam");
     $this->addOption("Bulgaria","Bulgaria");
     $this->addOption("Burkina Faso","Burkina Faso");
     $this->addOption("Burundi","Burundi");
     $this->addOption("Cambodia","Cambodia");
     $this->addOption("Cameroon","Cameroon");
     $this->addOption("Canada","Canada");
     $this->addOption("Cape Verde","Cape Verde");
     $this->addOption("Cayman Islands","Cayman Islands");
     $this->addOption("Central African Republic","Central African Republic");
     $this->addOption("Chad","Chad");
     $this->addOption("Chile","Chile");
     $this->addOption("China","China");
     $this->addOption("Christmas Island","Christmas Island");
     $this->addOption("Cocos (Keeling) Islands","Cocos (Keeling) Islands");
     $this->addOption("Colombia","Colombia");
     $this->addOption("Comoros","Comoros");
     $this->addOption("Congo","Congo");
     $this->addOption("Congo, The Democratic Republic of The","Congo, The Democratic Republic of The");
     $this->addOption("Cook Islands","Cook Islands");
     $this->addOption("Costa Rica","Costa Rica");
     $this->addOption("Cote D'ivoire","Cote D'ivoire");
     $this->addOption("Croatia","Croatia");
     $this->addOption("Cuba","Cuba");
     $this->addOption("Cyprus","Cyprus");
     $this->addOption("Czech Republic","Czech Republic");
     $this->addOption("Denmark","Denmark");
     $this->addOption("Djibouti","Djibouti");
     $this->addOption("Dominica","Dominica");
     $this->addOption("Dominican Republic","Dominican Republic");
     $this->addOption("Ecuador","Ecuador");
     $this->addOption("Egypt","Egypt");
     $this->addOption("El Salvador","El Salvador");
     $this->addOption("Equatorial Guinea","Equatorial Guinea");
     $this->addOption("Eritrea","Eritrea");
     $this->addOption("Estonia","Estonia");
     $this->addOption("Ethiopia","Ethiopia");
     $this->addOption("Falkland Islands (Malvinas)","Falkland Islands (Malvinas)");
     $this->addOption("Faroe Islands","Faroe Islands");
     $this->addOption("Fiji","Fiji");
     $this->addOption("Finland","Finland");
     $this->addOption("France","France");
     $this->addOption("French Guiana","French Guiana");
     $this->addOption("French Polynesia","French Polynesia");
     $this->addOption("French Southern Territories","French Southern Territories");
     $this->addOption("Gabon","Gabon");
     $this->addOption("Gambia","Gambia");
     $this->addOption("Georgia","Georgia");
     $this->addOption("Germany","Germany");
     $this->addOption("Ghana","Ghana");
     $this->addOption("Gibraltar","Gibraltar");
     $this->addOption("Greece","Greece");
     $this->addOption("Greenland","Greenland");
     $this->addOption("Grenada","Grenada");
     $this->addOption("Guadeloupe","Guadeloupe");
     $this->addOption("Guam","Guam");
     $this->addOption("Guatemala","Guatemala");
     $this->addOption("Guernsey","Guernsey");
     $this->addOption("Guinea","Guinea");
     $this->addOption("Guinea-bissau","Guinea-bissau");
     $this->addOption("Guyana","Guyana");
     $this->addOption("Haiti","Haiti");
     $this->addOption("Heard Island and Mcdonald Islands","Heard Island and Mcdonald Islands");
     $this->addOption("Holy See (Vatican City State)","Holy See (Vatican City State)");
     $this->addOption("Honduras","Honduras");
     $this->addOption("Hong Kong","Hong Kong");
     $this->addOption("Hungary","Hungary");
     $this->addOption("Iceland","Iceland");
     $this->addOption("India","India");
     $this->addOption("Indonesia","Indonesia");
     $this->addOption("Iran, Islamic Republic of","Iran, Islamic Republic of");
     $this->addOption("Iraq","Iraq");
     $this->addOption("Ireland","Ireland");
     $this->addOption("Isle of Man","Isle of Man");
     $this->addOption("Israel","Israel");
     $this->addOption("Italy","Italy");
     $this->addOption("Jamaica","Jamaica");
     $this->addOption("Japan","Japan");
     $this->addOption("Jersey","Jersey");
     $this->addOption("Jordan","Jordan");
     $this->addOption("Kazakhstan","Kazakhstan");
     $this->addOption("Kenya","Kenya");
     $this->addOption("Kiribati","Kiribati");
     $this->addOption("Korea, Democratic People's Republic of","Korea, Democratic People's Republic of");
     $this->addOption("Korea, Republic of","Korea, Republic of");
     $this->addOption("Kuwait","Kuwait");
     $this->addOption("Kyrgyzstan","Kyrgyzstan");
     $this->addOption("Lao People's Democratic Republic","Lao People's Democratic Republic");
     $this->addOption("Latvia","Latvia");
     $this->addOption("Lebanon","Lebanon");
     $this->addOption("Lesotho","Lesotho");
     $this->addOption("Liberia","Liberia");
     $this->addOption("Libyan Arab Jamahiriya","Libyan Arab Jamahiriya");
     $this->addOption("Liechtenstein","Liechtenstein");
     $this->addOption("Lithuania","Lithuania");
     $this->addOption("Luxembourg","Luxembourg");
     $this->addOption("Macao","Macao");
     $this->addOption("Macedonia, The Former Yugoslav Republic of","Macedonia, The Former Yugoslav Republic of");
     $this->addOption("Madagascar","Madagascar");
     $this->addOption("Malawi","Malawi");
     $this->addOption("Malaysia","Malaysia");
     $this->addOption("Maldives","Maldives");
     $this->addOption("Mali","Mali");
     $this->addOption("Malta","Malta");
     $this->addOption("Marshall Islands","Marshall Islands");
     $this->addOption("Martinique","Martinique");
     $this->addOption("Mauritania","Mauritania");
     $this->addOption("Mauritius","Mauritius");
     $this->addOption("Mayotte","Mayotte");
     $this->addOption("Mexico","Mexico");
     $this->addOption("Micronesia, Federated States of","Micronesia, Federated States of");
     $this->addOption("Moldova, Republic of","Moldova, Republic of");
     $this->addOption("Monaco","Monaco");
     $this->addOption("Mongolia","Mongolia");
     $this->addOption("Montenegro","Montenegro");
     $this->addOption("Montserrat","Montserrat");
     $this->addOption("Morocco","Morocco");
     $this->addOption("Mozambique","Mozambique");
     $this->addOption("Myanmar","Myanmar");
     $this->addOption("Namibia","Namibia");
     $this->addOption("Nauru","Nauru");
     $this->addOption("Nepal","Nepal");
     $this->addOption("Netherlands","Netherlands");
     $this->addOption("Netherlands Antilles","Netherlands Antilles");
     $this->addOption("New Caledonia","New Caledonia");
     $this->addOption("New Zealand","New Zealand");
     $this->addOption("Nicaragua","Nicaragua");
     $this->addOption("Niger","Niger");
     $this->addOption("Nigeria","Nigeria");
     $this->addOption("Niue","Niue");
     $this->addOption("Norfolk Island","Norfolk Island");
     $this->addOption("Northern Mariana Islands","Northern Mariana Islands");
     $this->addOption("Norway","Norway");
     $this->addOption("Oman","Oman");
     $this->addOption("Pakistan","Pakistan");
     $this->addOption("Palau","Palau");
     $this->addOption("Palestinian Territory, Occupied","Palestinian Territory, Occupied");
     $this->addOption("Panama","Panama");
     $this->addOption("Papua New Guinea","Papua New Guinea");
     $this->addOption("Paraguay","Paraguay");
     $this->addOption("Peru","Peru");
     $this->addOption("Philippines","Philippines");
     $this->addOption("Pitcairn","Pitcairn");
     $this->addOption("Poland","Poland");
     $this->addOption("Portugal","Portugal");
     $this->addOption("Puerto Rico","Puerto Rico");
     $this->addOption("Qatar","Qatar");
     $this->addOption("Reunion","Reunion");
     $this->addOption("Romania","Romania");
     $this->addOption("Russian Federation","Russian Federation");
     $this->addOption("Rwanda","Rwanda");
     $this->addOption("Saint Helena","Saint Helena");
     $this->addOption("Saint Kitts and Nevis","Saint Kitts and Nevis");
     $this->addOption("Saint Lucia","Saint Lucia");
     $this->addOption("Saint Pierre and Miquelon","Saint Pierre and Miquelon");
     $this->addOption("Saint Vincent and The Grenadines","Saint Vincent and The Grenadines");
     $this->addOption("Samoa","Samoa");
     $this->addOption("San Marino","San Marino");
     $this->addOption("Sao Tome and Principe","Sao Tome and Principe");
     $this->addOption("Saudi Arabia","Saudi Arabia");
     $this->addOption("Senegal","Senegal");
     $this->addOption("Serbia","Serbia");
     $this->addOption("Seychelles","Seychelles");
     $this->addOption("Sierra Leone","Sierra Leone");
     $this->addOption("Singapore","Singapore");
     $this->addOption("Slovakia","Slovakia");
     $this->addOption("Slovenia","Slovenia");
     $this->addOption("Solomon Islands","Solomon Islands");
     $this->addOption("Somalia","Somalia");
     $this->addOption("South Africa","South Africa");
     $this->addOption("South Georgia and The South Sandwich Islands","South Georgia and The South Sandwich Islands");
     $this->addOption("Spain","Spain");
     $this->addOption("Sri Lanka","Sri Lanka");
     $this->addOption("Sudan","Sudan");
     $this->addOption("Suriname","Suriname");
     $this->addOption("Svalbard and Jan Mayen","Svalbard and Jan Mayen");
     $this->addOption("Swaziland","Swaziland");
     $this->addOption("Sweden","Sweden");
     $this->addOption("Switzerland","Switzerland");
     $this->addOption("Syrian Arab Republic","Syrian Arab Republic");
     $this->addOption("Taiwan","Taiwan");
     $this->addOption("Tajikistan","Tajikistan");
     $this->addOption("Tanzania, United Republic of","Tanzania, United Republic of");
     $this->addOption("Thailand","Thailand");
     $this->addOption("Timor-leste","Timor-leste");
     $this->addOption("Togo","Togo");
     $this->addOption("Tokelau","Tokelau");
     $this->addOption("Tonga","Tonga");
     $this->addOption("Trinidad and Tobago","Trinidad and Tobago");
     $this->addOption("Tunisia","Tunisia");
     $this->addOption("Turkey","Turkey");
     $this->addOption("Turkmenistan","Turkmenistan");
     $this->addOption("Turks and Caicos Islands","Turks and Caicos Islands");
     $this->addOption("Tuvalu","Tuvalu");
     $this->addOption("Uganda","Uganda");
     $this->addOption("Ukraine","Ukraine");
     $this->addOption("United Arab Emirates","United Arab Emirates");
     $this->addOption("United Kingdom","United Kingdom");
     //$this->addOption("United States","United States");
     $this->addOption("United States Minor Outlying Islands","United States Minor Outlying Islands");
     $this->addOption("Uruguay","Uruguay");
     $this->addOption("Uzbekistan","Uzbekistan");
     $this->addOption("Vanuatu","Vanuatu");
     $this->addOption("Venezuela","Venezuela");
     $this->addOption("Viet Nam","Viet Nam");
     $this->addOption("Virgin Islands, British","Virgin Islands, British");
     $this->addOption("Virgin Islands, U.S.","Virgin Islands, U.S.");
     $this->addOption("Wallis and Futuna","Wallis and Futuna");
     $this->addOption("Western Sahara","Western Sahara");
     $this->addOption("Yemen","Yemen");
     $this->addOption("Zambia","Zambia");
     $this->addOption("Zimbabwe","Zimbabwe");
   }
}

?>
