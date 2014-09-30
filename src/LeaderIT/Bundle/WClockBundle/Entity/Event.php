<?php

namespace LeaderIT\Bundle\WClockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 */
class Event
{

    const ACTION_WORK = 100;
    const ACTION_BREAK = 200;
    const ACTION_LEAVE = 300;
    const ACTION_NONE = 0;

    const ACTION_WORK_TEXT = "начал работу";
    const ACTION_BREAK_TEXT =  "перерыв";
    const ACTION_LEAVE_TEXT = "завершил день";
    const ACTION_NONE_TEXT = "ничего";


    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $time;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param string $userId
     * @return Event
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Event
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Event
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     * @return Event
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    public function load($type, $time, $date = null, $id=0, $userId="test") {
        $this->time = $time;
        $this->date = $date;
        $this->type = $type;
        $this->userId = $userId;
        $this->id = $id;
    }

    public static function getActionText($action) {
        // int -> string
        //

        switch($action) {
            case self::ACTION_WORK:
                return self::ACTION_WORK_TEXT;
            case self::ACTION_BREAK:
                return self::ACTION_BREAK_TEXT;
            case self::ACTION_LEAVE:
                return self::ACTION_LEAVE_TEXT;
            default:
                return self::ACTION_NONE_TEXT;
        }
    }
}
