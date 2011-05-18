<?php

require_once('ISBN13Helper.class.php');

/**
 *  ISBN13Helper Class
 *
 *  @author Adrien Brault
 */
class ISBN13
{
  /*
   *  Class
   */
  
  static function EAN13ToISBN13($ean) {
    return ISBN13Helper::EAN13ToISBN13($ean);
  }
  
  
  /*
   *  Properties
   */
  
  protected $prefix;
  protected $registrationGroup;
  protected $registrant;
  protected $publication;
  protected $checksum;
  
  protected $code;
  
  
  /*
   *  Getters and setters.
   */
  
  public function getPrefix() {
    return $this->prefix;
  }
  
  protected function setPrefix($newPrefix) {
    $this->prefix = intval($newPrefix);
  }
  
  
  public function getRegistrationGroup() {
    return $this->registrationGroup;
  }
  
  protected function setRegistrationGroup($newRegistrationGroup) {
    $this->registrationGroup = intval($newRegistrationGroup);
  }
  
  
  public function getRegistrant() {
    return $this->registrant;
  }
  
  protected function setRegistrant($newRegistrant) {
    $this->registrant = intval($newRegistrant);
  }
  
  
  public function getPublication() {
    return $this->publication;
  }
  
  protected function setPublication($newPublication) {
    $this->publication = intval($newPublication);
  }
  
  
  public function getChecksum() {
    return $this->checksum;
  }
  
  protected function setChecksum($newChecksum) {
    $this->checksum = intval($newChecksum);
  }
  
  
  public function getCode($delimiter = '-') {
    if (!$this->code || $delimiter != '-') {
      $isbn = $this->getPrefix() ? $this->getPrefix().$delimiter : '';
      $isbn .= implode(array(
        $this->getRegistrationGroup(),
        $this->getRegistrant(),
        $this->getPublication(),
        $this->getChecksum(),
       ),
       $delimiter
      );
      
      if ($delimiter != '-') {
        return $isbn;
      }
      $this->code = $isbn;
    }
    
    return $this->code;
  }
  
  
  
  /*
   *  Public.
   */
  
  public function __construct($isbn) {
    if (!is_array($isbn)) {
      $isbn = explode('-', $isbn);
      if (count($isbn) < 4) {
        throw new Exception('The ISBN Code is not complete.');
      }
    }
    
    $this->setPrefix($isbn[0]);
    $this->setRegistrationGroup($isbn[1]);
    $this->setRegistrant($isbn[2]);
    $this->setPublication($isbn[3]);
    $this->calculateChecksum();
    
    if (isset($isbn[4]) && $this->getChecksum() != $isbn[4]) {
      throw new Exception('Invalid checksum.');
    }
    
    if (strlen($this->getCode('')) != 13) {
      throw new Exception('The isbn length is invalid.');
    }
  }
  
  public function __toString() {
    return $this->getCode();
  }
  
  public function getEAN13Value() {
    if ($this->getPrefix() == 978) {
      return implode(array(
        $this->getPrefix(),
        $this->getRegistrationGroup(),
        $this->getRegistrant(),
        $this->getPublication(),
        $this->getChecksum(),
      ));
    }
  }
  
  public function isValid() {
    return ISBN13Helper::isISBN13Valid($this);
  }
  
  
  /*
   *  Internal.
   */
  
  protected function calculateChecksum() {
    $isbnCode = $this->getCode('');
    $checksum = 0;
    for ($i = 0; $i < 12; $i++) {
      $checksum += substr($isbnCode, $i, 1) * ($i % 2 == 1 ? 3 : 1);
    }
    $this->setChecksum((10 - $checksum % 10) % 10);
  }
}
