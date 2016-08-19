<?php

/**
 *                          SOFTWARE USE PERMISSION
*
*  By downloading and accessing this software and associated documentation
*  files ("Software") you are granted the unrestricted right to deal in the
*  Software, including, without limitation the right to use, copy, modify,
*  publish, sublicense and grant such rights to third parties, subject to the
*  following conditions:
*
*  The following copyright notice and this permission notice shall be included
*  in all copies, modifications or substantial portions of this Software:
*  Copyright © 2016 GSM Association.
*
*  THE SOFTWARE IS PROVIDED "AS IS," WITHOUT WARRANTY OF ANY KIND, INCLUDING
*  BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
*  PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
*  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
*  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
*  IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
*  SOFTWARE. YOU AGREE TO INDEMNIFY AND HOLD HARMLESS THE AUTHORS AND COPYRIGHT
*  HOLDERS FROM AND AGAINST ANY SUCH LIABILITY.
*/
namespace MCSDK\Discovery;
use MCSDK\Constants\LinkRels;

class OperatorUrls
{
    private $_authorizationUrl;
    private $_requestTokenUrl;
    private $_userInfoUrl;
    private $_premiumInfoUrl;
    private $_JWKSUrl;
    private $_providerMetadataUrl;

    public function getAuthorizationUrl(){
        return $this->_authorizationUrl;
    }

    public function setAuthorizationUrl($_authorizationUrl){
        $this->_authorizationUrl = $_authorizationUrl;
    }

    public function getRequestTokenUrl(){
        return $this->_requestTokenUrl;
    }

    public function setRequestTokenUrl($_requestTokenUrl){
        $this->_requestTokenUrl = $_requestTokenUrl;
    }

    public function getUserInfoUrl(){
        return $this->_userInfoUrl;
    }

    public function setUserInfoUrl($_userInfoUrl){
        $this->_userInfoUrl = $_userInfoUrl;
    }

    public function getPremiumInfoUrl(){
        return $this->_premiumInfoUrl;
    }

    public function setPremiumInfoUrl($_premiumInfoUrl){
        $this->_premiumInfoUrl = $_premiumInfoUrl;
    }

    public function getJWKSUrl(){
        return $this->_JWKSUrl;
    }

    public function setJWKSUrl($_JWKSUrl){
        $this->_JWKSUrl = $_JWKSUrl;
    }

    public function getProviderMetadataUrl(){
        return $this->_providerMetadataUrl;
    }

    public function setProviderMetadataUrl($_providerMetadataUrl){
        $this->_providerMetadataUrl = $_providerMetadataUrl;
    }

    public static function Parse($links)
    {

        if (!isset($links["response"]["apis"]["operatorid"]["link"])) {
            return null;
        }

        $links = $links["response"]["apis"]["operatorid"]["link"];
        $operatorUrls = new OperatorUrls();
        $operatorUrls->setAuthorizationUrl(self::getUrl($links, LinkRels::AUTHORIZATION));
        $operatorUrls->setRequestTokenUrl(self::getUrl($links, LinkRels::TOKEN));
        $operatorUrls->setUserInfoUrl(self::getUrl($links, LinkRels::USERINFO));
        $operatorUrls->setPremiumInfoUrl(self::getUrl($links, LinkRels::PREMIUMINFO));
        $operatorUrls->setJWKSUrl(self::getUrl($links, LinkRels::JWKS));
        $operatorUrls->setProviderMetadataUrl(self::getUrl($links, LinkRels::OPENID_CONFIGURATION));

        return $operatorUrls;
    }

    private static function getUrl($links, $rel)
    {
        $key = array_search($rel, array_column($links, 'rel'));
        if ($key !== false) {
            return $links[$key]["href"];
        }
        return null;
    }
}