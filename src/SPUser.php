<?php
/**
 * This file is part of the SPOIL library.
 *
 * @author     Quetzy Garcia <quetzyg@impensavel.com>
 * @copyright  2014-2016
 *
 * For the full copyright and license information,
 * please view the LICENSE.md file that was distributed
 * with this source code.
 */

namespace Impensavel\Spoil;

use Impensavel\Spoil\Exception\SPBadMethodCallException;
use Impensavel\Spoil\Exception\SPRuntimeException;

class SPUser extends SPObject
{
    /**
     * SharePoint Site
     *
     * @var  SPSite
     */
    protected $site;

    /**
     * User Account
     *
     * @var  string
     */
    protected $account;

    /**
     * User Email
     *
     * @var  string
     */
    protected $email;

    /**
     * User Full Name
     *
     * @var  string
     */
    protected $fullName;

    /**
     * User First Name
     *
     * @var  string
     */
    protected $firstName;

    /**
     * User Last Name
     *
     * @var  string
     */
    protected $lastName;

    /**
     * User Title
     *
     * @var  string
     */
    protected $title;

    /**
     * User Picture (URL)
     *
     * @var  string
     */
    protected $picture;

    /**
     * User URL (profile)
     *
     * @var  string
     */
    protected $url;

    /**
     * SharePoint User constructor
     *
     * @param   SPSite $site    SharePoint Site
     * @param   array  $payload OData response payload
     * @param   array  $extra   Extra payload values to map
     * @throws  SPBadMethodCallException
     */
    public function __construct(SPSite $site, array $payload, array $extra = [])
    {
        $this->mapper = array_merge([
            'account'   => 'AccountName',
            'email'     => 'Email',
            'fullName'  => 'DisplayName',
            'firstName' => 'UserProfileProperties/4/Value',
            'lastName'  => 'UserProfileProperties/6/Value',
            'title'     => 'Title',
            'picture'   => 'PictureUrl',
            'url'       => 'PersonalUrl',
        ], $extra);

        $this->site = $site;

        $this->hydrate($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'account'    => $this->account,
            'email'      => $this->email,
            'full_name'  => $this->fullName,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'title'      => $this->title,
            'picture'    => $this->picture,
            'url'        => $this->url,
            'extra'      => $this->extra
        ];
    }

    /**
     * Get SharePoint User Account
     *
     * @return  string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get SharePoint User Email
     *
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get SharePoint User Full Name
     *
     * @return  string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Get SharePoint User First Name
     *
     * @return  string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get SharePoint User Last Name
     *
     * @return  string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get SharePoint User Title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get SharePoint User Picture (URL)
     *
     * @return  string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Get SharePoint User URL (profile)
     *
     * @return  string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the current (logged) SharePoint User
     *
     * @param   SPSite $site  SharePoint Site object
     * @param   array  $extra Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPUser
     */
    public static function getCurrent(SPSite $site, array $extra = [])
    {
        $json = $site->request('_api/SP.UserProfiles.PeopleManager/GetMyProperties', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        return new static($site, $json, $extra);
    }

    /**
     * Get a SharePoint User by Account
     *
     * @param   SPSite $site    SharePoint Site object
     * @param   string $account SharePoint User account
     * @param   array  $extra   Extra payload values to map
     * @throws  SPRuntimeException
     * @return  SPUser
     */
    public static function getByAccount(SPSite $site, $account, array $extra = [])
    {
        $json = $site->request('_api/SP.UserProfiles.PeopleManager/GetPropertiesFor(accountName=@v)', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query' => [
                '@v' => "'".$account."'",
            ],
        ], 'POST');

        return new static($site, $json, $extra);
    }
}
