<?php

/**
 * @author Luis Merino <luis@proudsugar.com>
 *
 * This an extension class for Mixpanel. There are reserved properties stored in
 * the cookie Mixpanel.js creates. Else, one can figure out these properties also
 * by using the provided methods.
 */

class MixpanelExtended {

    /*
     * Constants
     */
    const SET_QUEUE_KEY          = "__mps";
    const SET_ONCE_QUEUE_KEY     = "__mpso";
    const ADD_QUEUE_KEY          = "__mpa";
    const APPEND_QUEUE_KEY       = "__mpap";
    const DISTINCT_ID            = "distinct_id";
    // This key is deprecated, but we want to check for it to see whether aliasing is allowed.
    const PEOPLE_DISTINCT_ID_KEY = '$people_distinct_id';
    const ALIAS_ID_KEY           = "__alias";

    public static $RESERVED_PROPERTIES = array(
        self::SET_QUEUE_KEY,
        self::SET_ONCE_QUEUE_KEY,
        self::ADD_QUEUE_KEY,
        self::APPEND_QUEUE_KEY,
        self::DISTINCT_ID,
        self::PEOPLE_DISTINCT_ID_KEY,
        self::ALIAS_ID_KEY
    );
    public static $PEOPLE_PROPERTIES   = array('$os', '$browser', '$initial_referrer', '$initial_referring_domain');

    /**
     * The User-Agent HTTP header is stored in here.
     * @var string
     */
    private $userAgent = null;

    /**
     * The Referrer HTTP header is stored in here.
     * @var string
     */
    private $referrer = null;

    /**
     * The Mixpanel cookie value is stored in here.
     * @var string
     */
    private $mpCookie = null;

    /**
     * The only possible HTTP header that represent the
     * User-Agent string that is of use to us.
     *
     * @var string
     */
    private $uaHttpHeader = 'HTTP_USER_AGENT'; // The default User-Agent string.

    /**
     * The only possible HTTP header that represent the
     * Referrer string that is of use to us.
     *
     * @var string
     */
    private $refHttpHeader = 'HTTP_REFERER'; // The default Referer string.

    /**
     * The campaign parameters from Google.
     *
     * @var array
     */
    private $campaignKeywords = array('utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term');

    /**
     * Construct an instance of this class.
     *
     * @param string $mpCookie  Inject the Mixpanel cookie partial header, or the name of the cookie.
     *
     * @param string $userAgent Inject the User-Agent header. If null, will use HTTP_USER_AGENT.
     *
     * @param string $userAgent Inject the Referrer header. If null, will use HTTP_REFERRER.
     */
    public function __construct($mpCookie = array(), $userAgent = null, $referrer = null)
    {
        $this->setMpCookie($mpCookie);
        $this->setUserAgent($userAgent);
        $this->setReferrer($referrer);
    }

    /**
     * Set the User-Agent to be used.
     *
     * @param string $userAgent The user agent string to set.
     */
    public function setUserAgent($userAgent = null)
    {
        //use global _SERVER if $userAgent isn't defined
        if (!is_string($userAgent)) {
            $userAgent = $_SERVER[$this->uaHttpHeader];
        }

        $this->userAgent = $userAgent;
    }

    /**
     * Retrieve the User-Agent.
     *
     * @return string|null The user agent if it's set.
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set the Mixpanel cookie value to be used.
     *
     * @param string $mpCookie The Mixpanel cookie string to set.
     */
    public function setMpCookie($mpCookie = array())
    {
        if (is_string($mpCookie)) {
            if (isset($_COOKIE[$mpCookie])) {
                $mpCookie = json_decode(urldecode($_COOKIE[$mpCookie]), true);
            } else {
                $mpCookie = array();
            }
        }
        $this->mpCookie = $mpCookie;
    }

    /**
     * Retrieve the User-Agent.
     *
     * @return string|null The user agent if it's set.
     */
    public function getMpCookie()
    {
        return $this->mpCookie;
    }

    /**
     * Set the Referrer to be used.
     *
     * @param string $referrer The referrer string to set.
     */
    public function setReferrer($referrer = null)
    {
        //leave an empty string $referrer isn't defined
        if (!is_string($referrer)) {
            $referrer = $_SERVER[$this->refHttpHeader];
        }

        $this->referrer = $referrer;
    }

    /**
     * Retrieve the Referrer.
     *
     * @return string|null The referrer if it's set.
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Give the OS string by looking into the user agent string.
     *
     * @return string The OS contained in $userAgent
     */
    public function os()
    {
        $a = $this->getUserAgent();

        if (preg_match('/Windows/i', $a)) {
            if (preg_match('/Phone/', $a)) { return 'Windows Mobile'; }
            return 'Windows';
        } else if (preg_match('/(iPhone|iPad|iPhone)/', $a)) {
            return 'iOS';
        } else if (preg_match('/Android/', $a)) {
            return 'Android';
        } else if (preg_match('/(BlackBerry|PlayBook|BB10)/i', $a)) {
            return 'BlackBerry';
        } else if (preg_match('/Mac/i', $a)) {
            return 'Mac OS X';
        } else if (preg_match('/Linux/', $a)) {
            return 'Linux';
        } else {
            return '';
        }
    }

    /**
     * Give the Device id by looking into the user agent string.
     *
     * @return string The Device contained in $userAgent
     */
    public function device()
    {
        $a = $this->getUserAgent();

        if (preg_match('/iPad/', $a)) {
            return 'iPad';
        } else if (preg_match('/iPod/', $a)) {
            return 'iPod Touch';
        } else if (preg_match('/iPhone/', $a)) {
            return 'iPhone';
        } else if (preg_match('/(BlackBerry|PlayBook|BB10)/i', $a)) {
            return 'BlackBerry';
        } else if (preg_match('/Windows Phone/i', $a)) {
            return 'Windows Phone';
        } else if (preg_match('/Android/', $a)) {
            return 'Android';
        } else {
            return '';
        }
    }

    /**
     * This function detects which browser is running this script.
     * The order of the checks are important since many user agents
     * include key words used in later checks.
     */
    public function browser()
    {
        $a = $this->getUserAgent();

        if (preg_match("/Opera|Mobile.*OPR\/[0-9.]+|Coast\/[0-9.]+/", $a)) {
            if (is_int(strpos($a, "Mini"))) {
                return "Opera Mini";
            }
            return "Opera";
        } else if (preg_match('/(BlackBerry|PlayBook|BB10)/i', $a)) {
            return 'BlackBerry';
        } else if (is_int(strpos($a, 'FBIOS'))) {
            return "Facebook Mobile";
        } else if (is_int(strpos($a, 'Chrome'))) {
            return "Chrome";
        } else if (is_int(strpos($a, 'Apple'))) {
            if (is_int(strpos($a, 'Mobile'))) {
                return "Mobile Safari";
            }
            return "Safari";
        } else if (is_int(strpos($a, "Android"))) {
            return "Android Mobile";
        } else if (is_int(strpos($a, "Konqueror"))) {
            return "Konqueror";
        } else if (is_int(strpos($a, "Firefox"))) {
            return "Firefox";
        } else if (is_int(strpos($a, 'MSIE')) || is_int(strpos($a, "Trident/"))) {
            return "Internet Explorer";
        } else if (is_int(strpos($a, "Gecko"))) {
            return "Mozilla";
        } else {
            return "";
        }
    }

    /**
     * Give the referring domain given the referrer string.
     *
     * @return string The referrer domain contained in $referrer
     */
    public function referringDomain()
    {
        $split = preg_split('/\//', $this->getReferrer());
        if (count($split) >= 3) {
            return $split[2];
        }
        return '';
    }

    /**
     * Give the search engine name by looking into the user referrer string.
     *
     * @return string The search engine contained in $referrer
     */
    private function searchEngine($referrer)
    {
        if (preg_match('/https?:\/\/(.*)google.([^\/?]*)/', $referrer)) {
            return 'google';
        } else if (preg_match('/https?:\/\/(.*)bing.com/', $referrer)) {
            return 'bing';
        } else if (preg_match('/https?:\/\/(.*)yahoo\.com/', $referrer)) {
            return 'yahoo';
        } else if (preg_match('/https?:\/\/(\.*)duckduckgo\.com', $referrer)) {
            return 'duckduckgo';
        } else {
            return null;
        }
    }

    /**
     * Obtains the query parameter given a url and a param name.
     *
     * @return string The $param value in $url
     */
    private function getQueryParam($url, $param)
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        return $query[$param];
    }

    /**
     * Remove empty string values from properties array.
     *
     * @return array The params array
     */
    private function stripEmptyProperties(array $p)
    {
        $ret = array();
        foreach($p as $k => $v) {
            if (is_string($v) && strlen($v) > 0) { $ret[$k] = $v; }
        }
        return $ret;
    }

    /**
     * Give the search information given the referrer string.
     *
     * @return object The search information contained in $referrer
     */
    public function searchInfo()
    {
        $referrer = $this->getReferrer();
        $search = $this->searchEngine($referrer);
        $param = ($search != 'yahoo') ? 'q' : 'p';
        $ret = array();

        if (!empty($search)) {
            $ret['$search_engine'] = $search;
            $keyword = $this->getQueryParam($referrer, $param);
            if (strlen($keyword) > 0) {
                $ret["mp_keyword"] = $keyword;
            }
        }

        return $ret;
    }

    public function campaignParams($url = null)
    {
        $url = $url ? $url : $this->getReferrer();
        $params = array();
        $kw = '';

        foreach($this->campaignKeywords as $p) {
            $kw = $this->getQueryParam($url, $p);
            if (strlen($kw) > 0) {
                $params[$p] = $kw;
            }
        }
        return $params;
    }

    public function getProperties($mergeMpCookie = false)
    {
        $ret = array(
            '$os' => $this->os(),
            '$browser' => $this->browser(),
            '$device' => $this->device(),
            '$referrer' => $this->getReferrer(),
            '$referring_domain' => $this->referringDomain()
        );
        if ($mergeMpCookie) {
            $ret = array_merge($ret, $this->mpCookie);
            foreach (self::$RESERVED_PROPERTIES as $p) {
                unset($ret[$p]);
            }
        }
        return $this->stripEmptyProperties($ret);
    }

    public function getPeopleProperties($mergeMpCookie = false)
    {
        $ret = $this->getProperties($mergeMpCookie);
        return array_filter($ret, function($p) {
            return in_array(self::$PEOPLE_PROPERTIES, key($p));
        });
    }

}