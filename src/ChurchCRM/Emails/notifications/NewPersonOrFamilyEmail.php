<?php

namespace ChurchCRM\Emails;

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\PersonQuery;
use ChurchCRM\FamilyQuery;
use ChurchCRM\dto\SystemURLs;

class NewPersonOrFamilyEmail extends BaseEmail
{
    private $notificationSource;
    
    const FAMILY = 1;
    const PERSON = 2;
    
    private $notificationType;
    private $relatedId;
    
    public function __construct($notificationType,$RelatedId)
    {
       
        $this->notificationType = $notificationType;
        $this->relatedId = $RelatedId;
        $toAddresses = [];
        $recipientPeople = explode(",",SystemConfig::getValue("sNewPersonNotificationRecipients") );

        foreach($recipientPeople as $PersonID)
        {
          $Person = PersonQuery::create()->findOneById($PersonID);
          if($Person)
          {
            array_push($toAddresses,  $Person->getEmail());            
          }
        }

        parent::__construct($toAddresses);
        $this->mail->Subject = SystemConfig::getValue("sChurchName") . ": " . $this->getSubSubject();
        $this->mail->isHTML(true);
        $this->mail->msgHTML($this->buildMessage());
    }

    protected function getSubSubject()
    {
      if ($this->notificationType == self::PERSON)
      {
        return gettext("New Person Added");
      }
      else if ($this->notificationType == self::FAMILY)
      {
        return gettext("New Family Added");
      }
        
    }
   
     public function getTokens()
    {
        $myTokens =  [
            "toName" => gettext("Church Greeter")
        ];
        if ($this->notificationType == self::FAMILY)
        {
          $family = FamilyQuery::create()->findOneById($this->relatedId);
          $myTokens['Family'] = $family;
          $myTokens['Members'] = $family->getPeople();
          $myTokens['FamilyLink'] = SystemURLs::getURL()."/FamilyView.php?FamilyID=".$this->relatedId;
        }
        else if ($this->notificationType == self::PERSON)
        {
          $myTokens['Person'] = PersonQuery::create()->findOneById($this->relatedId);
          $myTokens['PersonLink'] = SystemURLs::getURL()."/PersonView.php?PersonID=".$this->relatedId;
        }
        
        return array_merge($this->getCommonTokens(), $myTokens);
    }
}
