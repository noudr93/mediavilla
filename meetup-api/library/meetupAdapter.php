<?php

/*
 * Copyright (c) 2015, Bart Bresse ,SONDER All rights reserved.
 * This software is licensed under the NEW BSD LICENSE
 * 
 */
require_once __DIR__.'/meetup.php';

/**
 * Description of meetupAdapter
 *
 * @author bart
 */
class meetupAdapter
{

    //put your code here
    private $access_token;
    private $refresh_token;
    private $expires;

    private $group_urlname;

    private $url;

    protected $apikey;
    protected $consumerkey;
    protected $consumersecret;

    protected $meetup;


    /**
     * Constructor
     *
     * @param $authType Either one of oauth or null.
     */
    public function __construct($authType/*, $apikey, $secret*/)
    {
        if ($authType == 'oauth') {
            $this->consumersecret = 'ke69uvaf8lfbjo4uhapsobnrkp';
            $this->consumerkey = 'km3hc1de4dq6q2hccserg072sk';
            $this->url = 'http://contourcraft.nl/wordpress/index.php/endpoint';
        }

        $this->apikey = '55773f11b6b484115756d7266b60';
        $this->meetup = new Meetup();
        $this->group_urlname = 'media-villa-arnhem';
    }

    /*public function __construct(MeetupAccount $meetupaccount = NULL,$callbackurl) {
        if (!is_null($meetupaccount)) {
            $this->refresh_token = $meetupaccount->refreshtoken;
            $this->access_token = $meetupaccount->acesstoken;
            $this->expires = $meetupaccount->expires;
        
            $this->consumerkey = $meetupaccount->consumerkey;
            $this->consumersecret = $meetupaccount->consumersecret;
            
            $this->url = $callbackurl;
        }
        else
        {
            if(strlen($this->access_token) < 1 || strlen($this->refresh_token) < 1 || strlen($this->expires) < 1)
            {
                return false;
            }
        }
    }*/

    public function validate()
    {

        if ($this->expires > time()) {
            $this->meetup = new Meetup(array("access_token" => $this->access_token));
            return true;
        } else {
            $this->signin($this->url, $this->consumerkey, $this->consumersecret);
            return false;
        }
    }

    protected function revalidate()
    {

    }

    /**
     * Refresh the authorization for the logged in user
     *
     * @param string $refreshToken Token to use for refreshing
     * @return array
     */
    public function refreshToken($refreshToken) {
        $this->meetup = new Meetup(array(
                'client_id' => $this->consumerkey,
                'redirect_url' => '//contourcraft.nl/user/getuser',
                'refresh_token' => $refreshToken
            )
        );

        $response = $this->meetup->refresh();

        $this->access_token = $response->access_token;
        $this->refresh_token = $response->refresh_token;
        $this->expires = time() + $response->expires;

        return array("access_token" => $response->access_token, "refresh_token" => $response->refresh_token, "expires" => time() + 3600);
    }

    /**
     * Meetup oauth sign in handler.
     *
     * @return array
     */
    public function signin()
    {

        if (!isset($_GET['code'])) {

            //authorize and go back to URI w/ code
            $this->meetup = new Meetup();
            $this->meetup->authorize(array(
                'client_id' => $this->consumerkey,
                'redirect_uri' => $this->url
            ));

        } else {
            //assuming we came back here...
            $this->meetup = new Meetup(
                array(
                    "client_id" => $this->consumerkey,
                    "client_secret" => $this->consumersecret,
                    "redirect_uri" => $this->url,
                    "code" => $_GET['code'] //passed back to us from meetup
                )
            );

            //get an access token
            $response = $this->meetup->access();
            //token
            //$this->meetup = new Meetup(array("access_token" => $response->access_token));
            //store details for later in case we need to do requests elsewhere
            //or refresh token
            //get all groups for this member
            //        $response = $meetup->getGroups(array('member_id' => '184301638'));
            //get all events for this member
            //        $response = $meetup->getEvents(array('member_id' => '184301638'));
            //  $meetup->
            //   print_r()

            $this->access_token = $response->access_token;
            $this->refresh_token = $response->refresh_token;
            $this->expires = time() + $response->expires_in;;
            //session_start();
            return array("access_token" => $response->access_token, "refresh_token" => $response->refresh_token, "expires" => time() + 3600);
        }
    }

    /**
     * Get all events for the configured meetup group
     *
     * @param string $status either one of upcoming or past.
     * @param int $page Total number of items to get per page
     * @return array
     */
    public function getEvents($status, $page)
    {
        $params = array(
            'status' => $status,
            'page' => $page,
            'group_urlname' => $this->group_urlname,
            'key' => $this->apikey
        );
        return $this->meetup->getEvents($params);
    }

    /**
     * Wrapper function to get all events
     *
     * @return array
     */
    public function getAllEvents() {
        $past_events = $this->getEvents('past', null);
        $upcoming_events = $this->getEvents('upcoming', null);
        $all_events =  $past_events + $upcoming_events;
        echo '<pre>'.print_r($all_events,1).'</pre>';
        return $all_events;
    }

    public function getCurrentUser() {
        if($this->validate()) {
            $params= array(
                'member_id' => 'self',
                'key' => $this->apikey
            );
            return $this->meetup->getMembers($params);
        }
        return false;
        //https://api.meetup.com/members?member_id=self&key=ABDE12345AB2324445
    }

    /**
     * Get information for a specific event
     *
     * @param int $id event ID to get info for
     * @return bool|array
     */
    public function getEvent($id)
    {
        if (!$this->validate()) {
            return false;
        }

        if(is_numeric($id)) {
            $params = array(
                'event_id' => $id
            );

            //return $this->meetup->get('/' . $urlname . '/events/' . $id, array());
            return $this->meetup->getEvents($params);
        }
    }

    /**
     * RSVP to event.
     *
     * @param int $event_id Event to rsvp for
     * @param String $state Either one of yes, no or waitlist.
     * @return bool
     */
    public function rsvp($event_id, $state) {
        if (!$this->validate()) {
            return false;
        }
        if(is_numeric($event_id) && ($state == 'yes' || $state == 'no' || $state = 'waitlist')) {
            $params = array(
                'event_id' => $event_id
            );
        }
    }


    /**
     * @param int $event_id Event id to get rsvps for
     * @return bool|mixed
     */
    public function getRSVPS($event_id) {
        if(!$this->validate()) {
            return false;
        }

        if(is_numeric($event_id)) {
            $params = array(
                'event_id' => $event_id
            );

            return $this->meetup->get('/2/rsvps', $event_id);
        }
    }

    /**
     * Get all group members
     *
     * @param $id group id to get members for
     * @return bool|mixed
     */
    public function getGroupMembers($id)
    {
        if (!$this->validate()) {
            return false;
        }

        return $this->meetup->get('/2/members/' . $id, array());
    }

    /**
     * Get specific member info
     *
     * @param $id Member id to get info for
     * @return bool|array
     */
    public function getMember($id)
    {
        if (!$this->validate()) {
            return false;
        }

        return $this->meetup->get('/2/member/' . $id, array());
    }

}
