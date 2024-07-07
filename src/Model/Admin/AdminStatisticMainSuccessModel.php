<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminStatisticMainSuccessModel implements ModelInterface
{
    private int $users;
    private int $categories;
    private int $audiobooks;
    private int $lastWeekRegistered;
    private int $lastWeekLogins;
    private int $lastWeekNotifications;
    private int $lastWeekTechnicalBreaks;

    /**
     * @param int $users
     * @param int $categories
     * @param int $audiobooks
     * @param int $lastWeekRegistered
     * @param int $lastWeekLogins
     * @param int $lastWeekNotifications
     * @param int $lastWeekTechnicalBreaks
     */
    public function __construct(int $users, int $categories, int $audiobooks, int $lastWeekRegistered, int $lastWeekLogins, int $lastWeekNotifications, int $lastWeekTechnicalBreaks)
    {
        $this->users = $users;
        $this->categories = $categories;
        $this->audiobooks = $audiobooks;
        $this->lastWeekRegistered = $lastWeekRegistered;
        $this->lastWeekLogins = $lastWeekLogins;
        $this->lastWeekNotifications = $lastWeekNotifications;
        $this->lastWeekTechnicalBreaks = $lastWeekTechnicalBreaks;
    }

    public function getUsers(): int
    {
        return $this->users;
    }

    public function setUsers(int $users): void
    {
        $this->users = $users;
    }
    
    public function getCategories(): int
    {
        return $this->categories;
    }

    public function setCategories(int $categories): void
    {
        $this->categories = $categories;
    }

    public function getAudiobooks(): int
    {
        return $this->audiobooks;
    }

    public function setAudiobooks(int $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    public function getLastWeekRegistered(): int
    {
        return $this->lastWeekRegistered;
    }

    public function setLastWeekRegistered(int $lastWeekRegistered): void
    {
        $this->lastWeekRegistered = $lastWeekRegistered;
    }
    
    public function getLastWeekLogins(): int
    {
        return $this->lastWeekLogins;
    }

    public function setLastWeekLogins(int $lastWeekLogins): void
    {
        $this->lastWeekLogins = $lastWeekLogins;
    }
    
    public function getLastWeekNotifications(): int
    {
        return $this->lastWeekNotifications;
    }

    public function setLastWeekNotifications(int $lastWeekNotifications): void
    {
        $this->lastWeekNotifications = $lastWeekNotifications;
    }

    public function getLastWeekTechnicalBreaks(): int
    {
        return $this->lastWeekTechnicalBreaks;
    }

    public function setLastWeekTechnicalBreaks(int $lastWeekTechnicalBreaks): void
    {
        $this->lastWeekTechnicalBreaks = $lastWeekTechnicalBreaks;
    }

}