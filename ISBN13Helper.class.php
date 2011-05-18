<?php

require_once('ISBN13.class.php');

/**
 *  ISBN13Helper Class
 *
 *  @author Adrien Brault
 */
class ISBN13Helper
{
  const RANGE_XML_FILE = 'RangeMessage.xml';
  
  /*
   *  CLASS
   */
   
  static $rangeData;
  
  static public function RangeData()
  {
    if (!self::$rangeData) {
      self::$rangeData = self::ParseRangeDataXML();
    }
    return self::$rangeData;
  }
  
  static protected function ParseRangeDataXML()
  {
    $rangeData = array();
    
    $dom = new DomDocument();
    $dom->load(self::RANGE_XML_FILE);
    
    $registrationGroupsElement = $dom->getElementsByTagName('RegistrationGroups')->item(0);
    $registrationGroupElements = $registrationGroupsElement->getElementsByTagName('Group');
    
    foreach ($registrationGroupElements as $groupElement) {
      $rangeData['ranges'][] = self::GetGroupArray($groupElement);
    }
    
    return $rangeData;
  }
  
  /*
   *  Parsing helper.
   */
  static protected function GetGroupArray(DOMElement $groupElement)
  {
    $groupData = array();
    
    $groupData['prefix'] = $groupElement->getElementsByTagName('Prefix')->item(0)->nodeValue;
    $groupData['agency'] = $groupElement->getElementsByTagName('Agency')->item(0)->nodeValue;
    
    
    $firstValues = explode('-', $groupData['prefix']);
    $groupData['ISBNPrefix'] = $firstValues[0];
    $groupData['registrationGroup'] = $firstValues[1];
    $groupData['prefixLength'] = strlen($firstValues[0]);
    $groupData['registrationGroupLength'] = strlen($firstValues[1]);
    
    $ruleElements = $groupElement->getElementsByTagName('Rule');
    
    foreach ($ruleElements as $ruleElement) {
      $rangesStr = $ruleElement->getElementsByTagName('Range')->item(0)->nodeValue;
      $ranges = explode('-', $rangesStr);
      
      $rule['length'] = $ruleElement->getElementsByTagName('Length')->item(0)->nodeValue;
      $rule['publicationLength'] = 9 - $groupData['registrationGroupLength'] - $rule['length'];
      
      $rule['start'] = substr($ranges[0], 0, $rule['length']);
      $rule['end'] = substr($ranges[1], 0, $rule['length']);
      
      $groupData['rules'][] = $rule;
    }
    
    return $groupData;
  }
  
  static public function isISBN13Valid(ISBN13 $isbn) {
    $rangeData = self::RangeData();
    $isbnPublicationLength = strlen($isbn->getPublication());
    foreach ($rangeData['ranges'] as $range) {
      if (strpos($isbn->getCode(), $range['prefix'].'-') === 0) { 
        foreach ($range['rules'] as $rule) {
          if ($isbn->getRegistrant() >= $rule['start']
              && $isbn->getRegistrant() <= $rule['end']
              && $isbnPublicationLength == $rule['publicationLength']) {
            return true;
          }
        }
        return false;
      }
    }
    return false;
  }
  
  static public function EAN13ToISBN13($ean) {
    if (strlen($ean) != 13) throw new Exception('An EAN13 must have a length of 13.');
    
    $rangeData = self::RangeData();
    foreach ($rangeData['ranges'] as $range) {
      $rangeEANPrefix = str_replace('-', '', $range['prefix']);
      if (strpos($ean, $rangeEANPrefix) === 0) { 
        foreach ($range['rules'] as $rule) {
          $eanRegistrantPart = substr($ean, $range['prefixLength'] + $range['registrationGroupLength'], $rule['length']);
          if (is_numeric($eanRegistrantPart)
              && $eanRegistrantPart >= $rule['start']
              && $eanRegistrantPart <= $rule['end']) {
            return new ISBN13(array(
              $range['ISBNPrefix'],
              $range['registrationGroup'],
              $eanRegistrantPart,
              substr($ean, $range['prefixLength'] + $range['registrationGroupLength'] + $rule['length'], $rule['publicationLength']),
            ));
          }
        }
      }
    }
    
    // We have not returned any ISBN code. The EAN is wrong.
    throw new Exception('The EAN13 could not be converted to ISBN13.');
  }
}
