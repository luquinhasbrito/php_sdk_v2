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

use MCSDK\Utils\RestResponse;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Discovery\IDiscoveryService;
use MCSDK\Discovery\DiscoveryOptions;
use MCSDK\MobileConnectConfig;
use MCSDK\Cache\ICache;
use MCSDK\Cache\Cache;
use MCSDK\Exceptions\MobileConnectEndpointHttpException;
use MCSDK\Authentication\AuthenticationService;
use MCSDK\Identity\IdentityService;
use MCSDK\MobileConnectWebInterface;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\Utils\HttpUtils;
use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\Authentication\JWKeySetService;
use MCSDK\Authentication\TokenValidationResult;

use Zend\Http\Request;
use Zend\Cache\Storage\ClearByNamespaceInterface;

class MobileConnectWebInterfaceMockTest extends PHPUnit_Framework_TestCase {
    const _validSdkSession = "zxcvbnm";
    const _invalidSdkSession = "mnbvcxz";
    private static $_responses = array();
    private static $_discovery;
    private static $_cache;
    private static $_restClient;
    private static $_config;
    private static $_authentication;
    private static $_identity;
    private static $_jwks;
    private static $_token;
    private static $_unauthorizedResponse;
    private static $_discoveryResponse;
    private static $_mobileConnect;
    private static $_request;

    public static function setUpBeforeClass()
    {
        self::$_unauthorizedResponse = new RestResponse(401, "");
        self::$_unauthorizedResponse->setHeaders(array (
            "WWW-Authenticate-Bearer error=\"invalid_request\", error_description=\"No Access Token\""
        ));

        self::$_responses["operator-selection"] = new RestResponse(202, "{\"links\":[{\"rel\":\"operatorSelection\",\"href\":\"http://discovery.sandbox2.mobileconnect.io/v2/discovery/users/operator-selection?session_id=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZWRpcmVjdFVybCI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMS8iLCJhcHBsaWNhdGlvbiI6eyJleHRlcm5hbF9pZCI6IjExMzgiLCJuYW1lIjoiY3NoYXJwLXNkayIsImtleXMiOnsic2FuZGJveCI6eyJrZXkiOiI2Njc0MmE4NS0yMjgyLTQ3NDctODgxZC1lZDViN2JkNzRkMmQiLCJzZWNyZXQiOiJmMTUxOTlmNC1iNjU4LTRlNTgtOGJiMy1lNDA5OTg4NzMzOTIifX0sInJlZGlyZWN0X3VyaSI6Imh0dHBzOi8vbG9jYWxob3N0OjgwMDEvIiwiZGV2ZWxvcGVyIjp7InBvcnRhbF91c2VyX2lkIjoiMTEzOCIsIm5hbWUiOiJOaWNob2xhcyBEb25vaG9lIiwiZW1haWwiOiJuaWNob2xhcy5kb25vaG9lQGJqc3MuY29tIiwicHJvZmlsZSI6Imh0dHBzOi8vZGV2ZWxvcGVyLm1vYmlsZWNvbm5lY3QuaW8vYXBpL3YxL3VzZXI_ZW1haWw9bmljaG9sYXMuZG9ub2hvZSU0MGJqc3MuY29tIiwidXBkYXRlZCI6IjIwMTYtMDQtMjBUMDk6MzQ6MThaIiwibXNpc2RucyI6WyI5NDE0ZTI1MmMzYjE1ZWUzMGIyN2NmYmQxNjkzN2UwNWJlMGQ1NWYwZGZjZGQ0MjM2OTg3NTU1MjQ3ZjU0YzUyIiwiZjYwZjFkZDU1YzUxMjE3ZTAwMTc4YWE3ZGIxM2Q5Njc4OGUxZmM0MzRkMGU2ZGZiZmI2NjVlYjU5NzU3MGIwZiJdLCJtc2lzZG5TaG9ydCI6WyI3NTc1IiwiMzMzMyJdLCJzbXNBdXRoIjp0cnVlLCJtY2MiOiI5MDEiLCJtbmMiOiIwMSIsImNvbnNlbnQiOmZhbHNlfX0sInVzZXIiOnsibmFtZSI6IjY2NzQyYTg1LTIyODItNDc0Ny04ODFkLWVkNWI3YmQ3NGQyZCIsInBhc3MiOiJmMTUxOTlmNC1iNjU4LTRlNTgtOGJiMy1lNDA5OTg4NzMzOTIifSwiaWF0IjoxNDYxMTY5MzA5fQ.2Lp0Xt9JXVZxNbnNq_RH-5KJPQ06qw6ttR4ZK3fwcQU\"}]}");
        self::$_responses["authentication"] = new RestResponse(200, "{\"ttl\":1466082848000,\"response\":{\"client_name\": \"test1\",\"client_id\":\"x-ZWRhNjU3OWI3MGIwYTRh\",\"client_secret\":\"x-NjQzZTBhZWM0YmQ4ZDQ5\",\"serving_operator\":\"demo_unitedkingdom\",\"country\":\"UnitedKingdom\",\"currency\":\"GBP\",\"apis\":{\"operatorid\":{\"link\":[{\"rel\":\"authorization\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/index.php/auth\"},{\"rel\":\"token\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/index.php/token\"},{\"rel\":\"userinfo\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/index.php/userinfo\"},{\"rel\":\"jwks\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/cert.jwk\"},{\"rel\":\"openid-configuration\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/discovery.php/openid-configuration\"}]}}},\"subscriber_id\":\"6c483ef529a86e5aa808f9cfdcb78ac3ec9f24aba27ea1a003476b0693751d89c3feacd3d2ff00c0e1e1cb683ff7de9ea87bdd775d4e79b7da5a4fbec509d918c1f804fdaf1fcaa9d1aae572bd19a12de7de2d695d004a3b2828be9b79e5f13a5c70a35adebedef138ab11440f8573fff53e59c8348caaf458716dbb53b4162d27737f290a8a759a4eab409af27685b3667659ce1f5b2194ab68953c0381126fc941eb0043c17647021d1e47a07cfde2e5e18c9e29ca01af1a8d2b3558d9853ffeed1cd9c8545e0d4c609db4ca318c02d10cddaf83bab927f81c4ca8bbb04da4dba273a4f76d3962e5a31a59f806067393823ae6702850726281352849209fe4\"}");
        self::$_responses["error"] = new RestResponse(200, "{\"error\":\"Not_Found_Entity\",\"description\":\"Operator Not Found\"}");
        self::$_responses["provider-metadata"] = new RestResponse(200, "{\"version\":\"3.0\",\"issuer\":\"https://reference.mobileconnect.io/mobileconnect\",\"authorization_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/auth\",\"token_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/token\",\"userinfo_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/userinfo\",\"check_session_iframe\":\"https://reference.mobileconnect.io/mobileconnect/opframe.php\",\"end_session_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/endsession\",\"jwks_uri\":\"https://reference.mobileconnect.io/mobileconnect/op.jwk\",\"scopes_supported\":[\"openid\",\"mc_authn\",\"mc_authz\",\"profile\",\"email\",\"address\"],\"response_types_supported\":[\"code\",\"code token\",\"code id_token\",\"token\",\"token id_token\",\"code token id_token\",\"id_token\"],\"grant_types_supported\":[\"authorization_code\"],\"acr_values_supported\":[\"2\",\"3\"],\"subject_types_supported\":[\"public\",\"pairwise\"],\"userinfo_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"userinfo_encryption_alg_values_supported\":[\"RSA1_5\",\"RSA-OAEP\"],\"userinfo_encryption_enc_values_supported\":[\"A128CBC-HS256\",\"A256CBC-HS512\",\"A128GCM\",\"A256GCM\"],\"id_token_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"id_token_encryption_alg_values_supported\":[\"RSA1_5\",\"RSA-OAEP\"],\"id_token_encryption_enc_values_supported\":[\"A128CBC-HS256\",\"A256CBC-HS512\",\"A128GCM\",\"A256GCM\"],\"request_object_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"request_object_encryption_alg_values_supported\":[\"RSA1_5\",\"RSA-OAEP\"],\"request_object_encryption_enc_values_supported\":[\"A128CBC-HS256\",\"A256CBC-HS512\",\"A128GCM\",\"A256GCM\"],\"token_endpoint_auth_methods_supported\":[\"client_secret_post\",\"client_secret_basic\",\"client_secret_jwt\",\"private_key_jwt\"],\"token_endpoint_auth_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"display_values_supported\":[\"page\"],\"claim_types_supported\":[\"normal\"],\"claims_supported\":[\"name\",\"given_name\",\"family_name\",\"middle_name\",\"nickname\",\"preferred_username\",\"profile\",\"picture\",\"website\",\"email\",\"email_verified\",\"gender\",\"birthdate\",\"zoneinfo\",\"locale\",\"phone_number\",\"phone_number_verified\",\"address\",\"updated_at\"],\"service_documentation\":\"https://reference.mobileconnect.io/mobileconnect/index.php/servicedocs\",\"claims_locales_supported\":[\"en-US\"],\"ui_locales_supported\":[\"en-US\"],\"require_request_uri_registration\":false,\"op_policy_uri\":\"https://reference.mobileconnect.io/mobileconnect/index.php/op_policy\",\"op_tos_uri\":\"https://reference.mobileconnect.io/mobileconnect/index.php/op_tos\",\"claims_parameter_supported\":true,\"request_parameter_supported\":true,\"request_uri_parameter_supported\":true,\"mobile_connect_version_supported\":[{\"openid\":\"mc_v1.1\"},{\"openid mc_authn\":\"mc_v1.2\"},{\"openid mc_authz\":\"mc_v1.2\"}],\"login_hint_methods_supported\":[\"MSISDN\",\"ENCR_MSISDN\",\"PCR\"]} ");
        self::$_responses["user-info"] = new RestResponse(200, "{\"sub\":\"411421B0-38D6-6568-A53A-DF99691B7EB6\",\"email\":\"test2@example.com\",\"email_verified\":true}");
        self::$_responses["jwks"] = new RestResponse(200, "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}");
        self::$_responses["token"] = new RestResponse(200, "{\"access_token\":\"966ad150-16c5-11e6-944f-43079d13e2f3\",\"token_type\":\"Bearer\",\"expires_in\":3600,\"id_token\":\"eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LVpXUmhOalUzT1dJM01HSXdZVFJoIiwiYXpwIjoieC1aV1JoTmpVM09XSTNNR0l3WVRSaCIsImlzcyI6Imh0dHBzOi8vcmVmZXJlbmNlLm1vYmlsZWNvbm5lY3QuaW8vbW9iaWxlY29ubmVjdCIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3LCJpYXQiOjE0NzEzMzk3MTB9.f0DkOkD6uQPvKZXf2uUHBmIpDaW84mlRmI3dexfMBFP9vk5HEXu-rxsLTtUCDX3QDp56nZTyQVdvGXrm6QG2ew20VSDdn3_-_Bx1oMO36WYpSve37l3eJXNNPiUSsWex72o4CpCeRd6F6u8GToF-F4rq1NwEf6WTGxtggE0O1NR0X-agPomdMvfGDwk0FXEIqd0lEmxBJI5PU3FQIILEDDjW2CCz62MqZEvPzvSnCAWtSqiDiuKNvfNDPD5oPqGMhZv4D2AuWmh9fztbsFIoM671Ug89N-8Pte7zE6hgSl98hZP9ak3YbLdYvqjbn9QY2hJbf0ceVkKnqNY7cTnb-A\"}");
        self::$_responses["token-revoked"] = new RestResponse(200, "");
        self::$_responses["unauthorized"] = self::$_unauthorizedResponse;

        self::$_restClient = new MockRestClient();
        self::$_cache = new Cache();
        self::$_discovery = new DiscoveryService(self::$_restClient, self::$_cache);
        self::$_authentication = new AuthenticationService(self::$_restClient);
        self::$_identity = new IdentityService(self::$_restClient);
        self::$_jwks = new JWKeysetService(self::$_restClient, self::$_cache);

        self::$_discoveryResponse = new DiscoveryResponse(self::$_responses["authentication"]);
        self::$_cache->AddKey(self::_validSdkSession, self::$_discoveryResponse);

        self::$_config = new MobileConnectConfig();
        self::$_config->setClientId("zxcvbnm");
        self::$_config->setClientSecret("asdfghjkl");
        self::$_config->setDiscoveryUrl("qwertyuiop");
        self::$_config->setRedirectUrl("http://qwertyuiop");

        self::$_mobileConnect = new MobileConnectWebInterface(self::$_discovery,self::$_authentication, self::$_identity, self::$_jwks, self::$_config);

        self::$_request = new Request();
        self::$_request->setMethod(Request::METHOD_GET);
        self::$_request->setUri("http://discovery.mobileconnect.io");
    }

    private function CompleteDiscovery()
    {
        self::$_restClient->QueueResponse(self::$_responses["authentication"]);
        self::$_restClient->QueueResponse(self::$_responses["provider-metadata"]);
        return self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::$_config->getRedirectUrl(), "111", "11", self::$_config);
    }

    public function testStartAuthenticationShouldUseAuthnWhenAuthzOptionsNotSet()
    {
        $discoveryResponse = $this->CompleteDiscovery();

        $result = self::$_mobileConnect->Authentication($discoveryResponse, "1111222233334444", "state", "nonce", new MobileConnectRequestOptions());

        $scope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals(MobileConnectResponseType::Authentication, $result->getResponseType());

        $this->assertContains("mc_authn", $scope);
        $this->assertNotContains("mc_authz", $scope);
    }

    public function testStartAuthenticationShouldUseAuthzWhenContextSet()
    {
        $discoveryResponse = $this->CompleteDiscovery();

        $options = new MobileConnectRequestOptions();
        $options->setContext("context");
        $result = self::$_mobileConnect->Authentication($discoveryResponse, "1111222233334444", "state", "nonce", $options);
        $scope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals(MobileConnectResponseType::Authentication, $result->getResponseType());
        $this->assertContains("mc_authz", $scope);
        $this->assertNotContains("mc_authn", $scope);
    }

    public function testStartAuthenticationShouldUseAuthzWhenAuthzScope()
    {
        $discoveryResponse = $this->CompleteDiscovery();
        $options = new MobileConnectRequestOptions();
        $options->setScope("mc_identity_phone");
        $options->setContext("context");
        $options->setBindingMessage("message");

        $result = self::$_mobileConnect->Authentication($discoveryResponse, "1111222233334444", "state", "nonce", $options);
        $scope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals(MobileConnectResponseType::Authentication, $result->getResponseType());
        $this->assertContains("mc_authz", $scope);
        $this->assertNotContains("mc_authn", $scope);
    }

    public function testStartAuthenticationShouldUseAuthzWhenMCProductScope()
    {
        $discoveryResponse = $this->CompleteDiscovery();
        $options = new MobileConnectRequestOptions();
        $options->setScope("mc_identity_phone");
        $options->setContext("context");
        $options->setBindingMessage("message");

        $result = self::$_mobileConnect->Authentication($discoveryResponse, "1111222233334444", "state", "nonce", $options);
        $scope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals(MobileConnectResponseType::Authentication, $result->getResponseType());
        $this->assertContains("mc_authz", $scope);
        $this->assertContains("mc_identity_phone", $scope);
        $this->assertNotContains("mc_authn", $scope);
    }

    public function testStartAuthenticationShouldSetClientNameWhenAuthz()
    {
        $discoveryResponse = $this->CompleteDiscovery();
        $expected = "test1";

        $options = new MobileConnectRequestOptions();
        $options->setScope("mc_identity_phone");
        $options->setContext("context");
        $options->setBindingMessage("message");

        $result = self::$_mobileConnect->Authentication($discoveryResponse, "1111222233334444", "state", "nonce", $options);
        $clientName = HttpUtils::ExtractQueryValue($result->getUrl(), "client_name");

        $this->assertEquals($expected, $clientName);
    }

    public function testRequestUserInfoReturnsUserInfo()
    {
        self::$_restClient->queueResponse(self::$_responses["user-info"]);

        $result = self::$_mobileConnect->RequestUserInfoByDiscoveryResponse(self::$_discoveryResponse, "zaqwsxcderfvbgtyhnmjukilop", new MobileConnectRequestOptions());

        $this->assertNotNull($result->getIdentityResponse());
        $this->assertEquals(MobileConnectResponseType::UserInfo, $result->getResponseType());
    }

    public function testRequestUserInfoReturnsErrorWhenNoUserInfoUrl()
    {
        self::$_discoveryResponse->getOperatorUrls()->setUserInfoUrl(null);

        $result = self::$_mobileConnect->RequestUserInfoByDiscoveryResponse(self::$_discoveryResponse, "zaqwsxcderfvbgtyhnmjukilop", new MobileConnectRequestOptions());

        $this->assertNull($result->getIdentityResponse());
        $this->assertNotNull($result->getErrorCode());
        $this->assertNotNull($result->getErrorMessage());
        $this->assertEquals(MobileConnectResponseType::Error, $result->getResponseType());
    }

    public function testRequestUserInfoShouldUseSdkSessionCache()
    {
        self::$_restClient->queueResponse(self::$_responses["user-info"]);

        $result = self::$_mobileConnect->RequestUserInfo(self::_validSdkSession, "zaqwsxcderfvbgtyhnmjukilop", new MobileConnectRequestOptions());

        $this->assertNotNull($result->getIdentityResponse());
        $this->assertEquals(MobileConnectResponseType::UserInfo, $result->getResponseType());
    }

    public function testRequestTokenShouldReturnErrorForInvalidSession()
    {
        $result = self::$_mobileConnect->RequestToken(self::_invalidSdkSession, "http://localhost", "state", "nonce");

        $this->assertEquals(MobileConnectResponseType::Error, $result->getResponseType());
        $this->assertEquals("sdksession_not_found", $result->getErrorCode());
    }

    public function testRequestTokenShouldReturnErrorForCacheDisabled()
    {
        self::$_config->setCacheResponsesWithSessionId(false);
        self::$_mobileConnect = new MobileConnectWebInterface(self::$_discovery, self::$_authentication, self::$_identity, self::$_jwks, self::$_config);

        $result = self::$_mobileConnect->RequestToken(self::_invalidSdkSession, "http://localhost", "state", "nonce", new MobileConnectRequestOptions());

        $this->assertEquals(MobileConnectResponseType::Error, $result->getResponseType());
        $this->assertEquals("cache_disabled", $result->getErrorCode());
    }

    public function testRequestTokenAcceptsValidToken()
    {
        self::$_restClient->queueResponse(self::$_responses["jwks"]);
        self::$_restClient->queueResponse(self::$_responses["token"]);
        $result = self::$_mobileConnect->RequestTokenByDiscoveryResponse(self::$_discoveryResponse, "http://localhost:8001/?code=123123123456&state=zxcvbnm", "zxcvbnm", "1234567890", null);

        $this->assertEquals(MobileConnectResponseType::Complete, $result->getResponseType());
    }

    public function testRequestTokenAcceptInvalidTokenIfFlaggedAsAcceptedResult()
    {
        $options = new MobileConnectRequestOptions();
        self::$_restClient->queueResponse(self::$_responses["jwks"]);
        self::$_restClient->queueResponse(self::$_responses["token"]);

        $result = self::$_mobileConnect->RequestTokenByDiscoveryResponse(self::$_discoveryResponse, "http://localhost:8001/?code=123123123456&state=zxcvbnm", "zxcvbnm", "12345678", $options);

        $this->assertEquals(MobileConnectResponseType::Complete, $result->getResponseType());
    }

    public function testRefreshTokenShouldUseRefreshTokenUrl() {
        self::$_discoveryResponse->getOperatorUrls()->setRefreshTokenUrl("http://refresh");
        self::$_discoveryResponse->getOperatorUrls()->setRequestTokenUrl("http://request");
        self::$_restClient->queueResponse(self::$_responses["token"]);
        self::$_restClient->queueResponse(self::$_responses["error"]);

        $result = self::$_mobileConnect->RefreshTokenByDiscoveryResponse("token", self::$_discoveryResponse);

        $this->assertEquals(MobileConnectResponseType::Complete, $result->getResponseType());
    }

    public function testRefreshTokenShouldUseRequestTokenUrlWhenNoRefreshUrl() {
        self::$_discoveryResponse->getOperatorUrls()->setRefreshTokenUrl(null);
        self::$_discoveryResponse->getOperatorUrls()->setRequestTokenUrl("http://request");
        self::$_restClient->queueResponse(self::$_responses["error"]);
        self::$_restClient->queueResponse(self::$_responses["token"]);

        $result = self::$_mobileConnect->RefreshTokenByDiscoveryResponse("token", self::$_discoveryResponse);
        var_dump($result);
        $this->assertEquals(MobileConnectResponseType::Complete, $result->getResponseType());
    }

    public function testRefreshTokenShouldReturnErrorWhenNoRefreshOrRequestUrl()
    {
        self::$_discoveryResponse->getOperatorUrls()->setRefreshTokenUrl(null);
        self::$_discoveryResponse->getOperatorUrls()->setRequestTokenUrl(null);

        $result = self::$_mobileConnect->RefreshTokenByDiscoveryResponse("token", self::$_discoveryResponse);

        $this->assertEquals(MobileConnectResponseType::Error, $result->getResponseType());
        $this->assertEquals($result->getErrorCode(), "not_supported");
    }

    public function testRevokeTokenShouldReturnOk()
    {
        self::$_discoveryResponse->getOperatorUrls()->setRevokeTokenUrl("http://revoke");
        self::$_restClient->QueueResponse(self::$_responses["token-revoked"]);

        $result = self::$_mobileConnect->RevokeTokenByDiscoveryResponse("token", null, self::$_discoveryResponse);

        $this->assertEquals(MobileConnectResponseType::TokenRevoked, $result->getResponseType());
        $this->assertNull($result->getErrorCode());
        $this->assertNull($result->getErrorMessage());
    }

    public function testRevokeTokenShouldReturnErrorIfNoUrlAvailable()
    {
        self::$_discoveryResponse->getOperatorUrls()->setRevokeTokenUrl(null);

        $result = self::$_mobileConnect->RevokeTokenByDiscoveryResponse("token", null, self::$_discoveryResponse);

        $this->assertEquals(MobileConnectResponseType::Error, $result->getResponseType());
        $this->assertEquals($result->getErrorCode(), "not_supported");
    }
}
