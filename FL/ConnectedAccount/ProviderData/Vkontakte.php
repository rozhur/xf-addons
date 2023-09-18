<?php

namespace ZD\FL\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

class Vkontakte extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'users.get?fields=first_name,last_name,sex,screen_name,site,bdate,is_closed,photo_big&v=5.124';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('response')[0]['id'];
    }

    public function getUsername() {
        $response = $this->requestFromEndpoint('response')[0];
        if (isset($response['screen_name']) && $response['screen_name'] != 'id' . $response['id'])
        {
            return $response['screen_name'];
        }
        return $response['first_name'];
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->requestFromEndpoint('response')[0]['first_name'];
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->requestFromEndpoint('response')[0]['last_name'];
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        $response = $this->requestFromEndpoint('response')[0];
        return isset($response['email']) ?? null;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->requestFromEndpoint('response')[0]['sex'];
    }

    /**
     * @return mixed
     */
    public function getScreenName()
    {
        return $this->requestFromEndpoint('response')[0]['screen_name'];
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->requestFromEndpoint('response')[0]['site'];
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        return 'https://vk.com/' . $this->getScreenName();
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('response')[0]['photo_big'];
    }

    /**
     * @return array|bool|null
     */
    public function getDob()
    {
        $birthday = $this->requestFromEndpoint('response')[0]['bdate'];
        if ($birthday)
        {
            return $this->prepareBirthday($birthday, 'd.m.y');
        }

        return null;
    }
}