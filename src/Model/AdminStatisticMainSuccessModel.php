<?php

namespace App\Model;

class AdminStatisticMainSuccessModel implements ModelInterface
{
    private int $users;
    private int $categories;
    private int $audiobooks;
    private int $lastWeekRegistered;
    private int $lastWeekLogins;
    private int $lastWeekNotifications;

    /**
     * @param int $users
     * @param int $categories
     * @param int $audiobooks
     * @param int $lastWeekRegistered
     * @param int $lastWeekLogins
     * @param int $lastWeekNotifications
     */
    public function __construct(int $users, int $categories, int $audiobooks, int $lastWeekRegistered, int $lastWeekLogins, int $lastWeekNotifications)
    {
        $this->users = $users;
        $this->categories = $categories;
        $this->audiobooks = $audiobooks;
        $this->lastWeekRegistered = $lastWeekRegistered;
        $this->lastWeekLogins = $lastWeekLogins;
        $this->lastWeekNotifications = $lastWeekNotifications;
    }

    /**
     * @return int
     */
    public function getUsers(): int
    {
        return $this->users;
    }

    /**
     * @param int $users
     */
    public function setUsers(int $users): void
    {
        $this->users = $users;
    }

    /**
     * @return int
     */
    public function getCategories(): int
    {
        return $this->categories;
    }

    /**
     * @param int $categories
     */
    public function setCategories(int $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return int
     */
    public function getAudiobooks(): int
    {
        return $this->audiobooks;
    }

    /**
     * @param int $audiobooks
     */
    public function setAudiobooks(int $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    /**
     * @return int
     */
    public function getLastWeekRegistered(): int
    {
        return $this->lastWeekRegistered;
    }

    /**
     * @param int $lastWeekRegistered
     */
    public function setLastWeekRegistered(int $lastWeekRegistered): void
    {
        $this->lastWeekRegistered = $lastWeekRegistered;
    }

    /**
     * @return int
     */
    public function getLastWeekLogins(): int
    {
        return $this->lastWeekLogins;
    }

    /**
     * @param int $lastWeekLogins
     */
    public function setLastWeekLogins(int $lastWeekLogins): void
    {
        $this->lastWeekLogins = $lastWeekLogins;
    }

    /**
     * @return int
     */
    public function getLastWeekNotifications(): int
    {
        return $this->lastWeekNotifications;
    }

    /**
     * @param int $lastWeekNotifications
     */
    public function setLastWeekNotifications(int $lastWeekNotifications): void
    {
        $this->lastWeekNotifications = $lastWeekNotifications;
    }

}