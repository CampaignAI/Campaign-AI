<?php

require_once 'vendor/autoload.php';

use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsException;
use Google\Ads\GoogleAds\Util\V15\ResourceNames;
use Google\Ads\GoogleAds\V15\Enums\KeywordPlanNetworkEnum\KeywordPlanNetwork;
use Google\Ads\GoogleAds\V15\Errors\GoogleAdsError;
use Google\Ads\GoogleAds\V15\Services\GenerateKeywordIdeaResult;
use Google\Ads\GoogleAds\V15\Services\KeywordAndUrlSeed;
use Google\Ads\GoogleAds\V15\Services\KeywordSeed;
use Google\Ads\GoogleAds\V15\Services\UrlSeed;
use Google\Ads\GoogleAds\V15\Enums\KeywordPlanCompetitionLevelEnum\KeywordPlanCompetitionLevel;
use GetOpt\GetOpt;
use Google\Ads\GoogleAds\Examples\Utils\ArgumentNames;
use Google\Ads\GoogleAds\Examples\Utils\ArgumentParser;
use Google\Ads\GoogleAds\Examples\Utils\Helper;
use Google\Ads\GoogleAds\Lib\Configuration;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsServerStreamDecorator;
use Google\Ads\GoogleAds\Util\FieldMasks;
use Google\Ads\GoogleAds\V15\Common\AdImageAsset;
use Google\Ads\GoogleAds\V15\Common\AdTextAsset;
use Google\Ads\GoogleAds\V15\Common\IpBlockInfo;
use Google\Ads\GoogleAds\V15\Common\CustomAudienceInfo;
use Google\Ads\GoogleAds\V15\Common\ExpandedTextAdInfo;
use Google\Ads\GoogleAds\V15\Common\ImageAsset;
use Google\Ads\GoogleAds\V15\Common\KeywordInfo;
use Google\Ads\GoogleAds\V15\Common\ManualCpc;
use Google\Ads\GoogleAds\V15\Common\ResponsiveDisplayAdInfo;
use Google\Ads\GoogleAds\V15\Common\ResponsiveSearchAdInfo;
use Google\Ads\GoogleAds\V15\Common\TargetCpa;
use Google\Ads\GoogleAds\V15\Enums\AdGroupAdStatusEnum\AdGroupAdStatus;
use Google\Ads\GoogleAds\V15\Enums\AdGroupCriterionStatusEnum\AdGroupCriterionStatus;
use Google\Ads\GoogleAds\V15\Enums\AdGroupStatusEnum\AdGroupStatus;
use Google\Ads\GoogleAds\V15\Enums\AdGroupTypeEnum\AdGroupType;
use Google\Ads\GoogleAds\V15\Enums\AdTypeEnum\AdType;
use Google\Ads\GoogleAds\V15\Enums\AdvertisingChannelSubTypeEnum\AdvertisingChannelSubType;
use Google\Ads\GoogleAds\V15\Enums\AdvertisingChannelTypeEnum\AdvertisingChannelType;
use Google\Ads\GoogleAds\V15\Enums\AssetTypeEnum\AssetType;
use Google\Ads\GoogleAds\V15\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Google\Ads\GoogleAds\V15\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V15\Enums\CriterionTypeEnum\CriterionType;
use Google\Ads\GoogleAds\V15\Enums\CustomAudienceMemberTypeEnum\CustomAudienceMemberType;
use Google\Ads\GoogleAds\V15\Enums\CustomAudienceStatusEnum\CustomAudienceStatus;
use Google\Ads\GoogleAds\V15\Enums\CustomAudienceTypeEnum\CustomAudienceType;
use Google\Ads\GoogleAds\V15\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V15\Enums\RecommendationTypeEnum\RecommendationType;
use Google\Ads\GoogleAds\V15\Enums\ServedAssetFieldTypeEnum\ServedAssetFieldType;
use Google\Ads\GoogleAds\V15\Resources\Ad;
use Google\Ads\GoogleAds\V15\Resources\AdGroup;
use Google\Ads\GoogleAds\V15\Resources\AdGroupAd;
use Google\Ads\GoogleAds\V15\Resources\AdGroupCriterion;
use Google\Ads\GoogleAds\V15\Resources\Asset;
use Google\Ads\GoogleAds\V15\Resources\Campaign;
use Google\Ads\GoogleAds\V15\Resources\Campaign\NetworkSettings;
use Google\Ads\GoogleAds\V15\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V15\Resources\CampaignCriterion;
use Google\Ads\GoogleAds\V15\Resources\CustomAudience;
use Google\Ads\GoogleAds\V15\Resources\CustomAudienceMember;
use Google\Ads\GoogleAds\V15\Services\AdGroupAdOperation;
use Google\Ads\GoogleAds\V15\Services\AdGroupCriterionOperation;
use Google\Ads\GoogleAds\V15\Services\AdGroupOperation;
use Google\Ads\GoogleAds\V15\Services\AdOperation;
use Google\Ads\GoogleAds\V15\Services\AssetOperation;
use Google\Ads\GoogleAds\V15\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V15\Services\CampaignCriterionOperation;
use Google\Ads\GoogleAds\V15\Services\CampaignOperation;
use Google\Ads\GoogleAds\V15\Services\CustomAudienceOperation;
use Google\Ads\GoogleAds\V15\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V15\Services\MutateAdGroupAdsResponse;
use Google\Ads\GoogleAds\V15\Services\MutateAdGroupsResponse;
use Google\Ads\GoogleAds\V15\Services\MutateAssetResult;
use Google\Ads\GoogleAds\V15\Services\MutateCampaignBudgetsResponse;
use Google\Ads\GoogleAds\V15\Services\MutateCampaignsResponse;
use Google\ApiCore\ApiException;
use Google\Protobuf\Internal\RepeatedField;
use Google\Ads\GoogleAds\V15\Resources\CustomerClient;
use Google\Ads\GoogleAds\V15\Services\CustomerServiceClient;
use Google\Ads\GoogleAds\V15\Services\CustomerUserAccessInvitationServiceClient;
use Google\Ads\GoogleAds\V15\Resources\Customer;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Google\Ads\GoogleAds\V15\Enums\AccessRoleEnum\AccessRole;
use Google\Ads\GoogleAds\V15\Resources\CustomerUserAccessInvitation;
use Google\Ads\GoogleAds\V15\Services\CustomerUserAccessInvitationOperation;
use Google\Ads\GoogleAds\V15\Resources\Recommendation;
use Google\Ads\GoogleAds\V15\Services\ApplyRecommendationOperation;
use Google\Ads\GoogleAds\V15\Enums\ManagerLinkStatusEnum\ManagerLinkStatus;
use Google\Ads\GoogleAds\V15\Resources\CustomerClientLink;
use Google\Ads\GoogleAds\V15\Resources\CustomerManagerLink;
use Google\Ads\GoogleAds\V15\Services\CustomerClientLinkOperation;
use Google\Ads\GoogleAds\V15\Services\CustomerManagerLinkOperation;
use Google\Ads\GoogleAds\V15\Services\GenerateKeywordIdeasRequest;
use Google\Ads\GoogleAds\V15\Common\DeviceInfo;
use Google\Ads\GoogleAds\V15\Enums\DeviceEnum\Device;
use Google\Ads\GoogleAds\V15\Resources\AdGroupBidModifier;
use Google\Ads\GoogleAds\V15\Services\AdGroupBidModifierOperation;

class Adwords
{
    protected $config;
    protected $managerSession;
    protected $adwordsServices;
    protected $managerCustomerId;
    public $filePath;
    
    const PAGE_LIMIT = 500;

    public function __construct($config, $managerCustomerId)
    {
        $this->config = $config;
        $this->managerCustomerId = $managerCustomerId;
    }

    public function createClient($clientCustomerId, $refresh_token=null)
    {        
        $config = new Configuration($this->config);
        $builder = new OAuth2TokenBuilder();        
        $oAuth2Credential = $builder
            ->from($config)
            ->build();        
        $logger = new Logger("google logger");
        $logger->pushHandler(new StreamHandler("error.log", Logger::DEBUG));
        $logLevel = "DEBUG";        
        $googleAdsClient = (new GoogleAdsClientBuilder())
            ->withOAuth2Credential($oAuth2Credential)
            ->withDeveloperToken($this->config['OAUTH2']['developerToken'])
            ->withLoginCustomerId($clientCustomerId)
                ->withLogger($logger)->withLogLevel($logLevel)
            ->build();        
        return $googleAdsClient;        
    }
    
    public function GetAccountInfo(GoogleAdsClient $googleAdsClient, int $customerId)
    { 
         $query = 'SELECT customer.id, '
            . 'customer.descriptive_name, '
            . 'customer.currency_code, '
            . 'customer.time_zone, '
            . 'customer.tracking_url_template, '
            . 'customer.auto_tagging_enabled '
            . 'FROM customer '
            // Limits to 1 to clarify that selecting from the customer resource will always return
            // only one row, which will be for the customer ID specified in the request.
            . 'LIMIT 1';
        // Issues a search request to get the Customer object from the single row of the response
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        /** @var Customer $customer */
        $customer = $googleAdsServiceClient->search($customerId, $query)
            ->getIterator()
            ->current()
            ->getCustomer();

        // Print information about the account.
        printf(
            "Customer with ID %d, descriptive name '%s', currency code '%s', timezone '%s', "
            . "tracking URL template '%s' and auto tagging enabled '%s' was retrieved.%s",
            $customer->getId(),
            $customer->getDescriptiveName(),
            $customer->getCurrencyCode(),
            $customer->getTimeZone(),
            is_null($customer->getTrackingUrlTemplate())
                ? 'N/A' : $customer->getTrackingUrlTemplate(),
            $customer->getAutoTaggingEnabled() ? 'true' : 'false',
            PHP_EOL
        );
    }
    
    
    public function AddBlockIP(GoogleAdsClient $googleAdsClient, int $customerId, int $campaignId,  string $blockip) 
    {         
        $ipblock = new IpBlockInfo([
            'ip_address' => $blockip
                ]);
         $campaignCriterion = new CampaignCriterion([
            'campaign' => ResourceNames::forCampaign($customerId, $campaignId),
            'ip_block' => $ipblock,
            'negative' => true
        ]);
        $campaignCriterionOperation = new CampaignCriterionOperation();
        $campaignCriterionOperation->setCreate($campaignCriterion);        
        $campaignCriterionServiceClient = $googleAdsClient->getCampaignCriterionServiceClient();
        $response = $campaignCriterionServiceClient->mutateCampaignCriteria($customerId, [$campaignCriterionOperation]);		
        return $response;
    }
    
    public function RemBlockIP(GoogleAdsClient $googleAdsClient, int $customerId, int $campaignId,  string $ipaddress) 
    {
        $ipadd = $ipaddress.'/32';
        $query = "SELECT campaign_criterion.criterion_id, campaign_criterion.type, campaign_criterion.ip_block.ip_address FROM campaign_criterion WHERE campaign_criterion.ip_block.ip_address='".$ipadd."'";		
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $response = $googleAdsServiceClient->search($customerId, $query);
        foreach ($response->iterateAllElements() as $googleAdsRow) {    			
            $criterion_id = $googleAdsRow->getCampaignCriterion()->getCriterionId();
        }
        if(!is_null($criterion_id))
        {
        $campaignCriterionResourceName = ResourceNames::forCampaignCriterion($customerId, $campaignId, $criterion_id);        
        $campaignCriterionOperation = new CampaignCriterionOperation();
        $campaignCriterionOperation->setRemove($campaignCriterionResourceName);
        $campaignCriterionServiceClient = $googleAdsClient->getCampaignCriterionServiceClient();
        $result = $campaignCriterionServiceClient->mutateCampaignCriteria($customerId, [$campaignCriterionOperation]);
        }
        else
        {
            echo 'Not Found.';
        }
    }
    
    public function ListCampaigns(GoogleAdsClient $googleAdsClient, int $customerId) 
    {
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $query = 'SELECT campaign.id, campaign.name, campaign.status FROM campaign ORDER BY campaign.id';
        $stream =  $googleAdsServiceClient->search($customerId, $query);
        $campaign_array = [];
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $campaign_array[] = array('name'=>$googleAdsRow->getCampaign()->getName(),'id'=>$googleAdsRow->getCampaign()->getId());
        }
        return $campaign_array;
    }
    
    public function GetAdgroupName(GoogleAdsClient $googleAdsClient, int $customerId, int $adgroupId) 
    {
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $query = 'SELECT ad_group.name, ad_group.id FROM ad_group WHERE ad_group.id ='.$adgroupId;
        $stream =  $googleAdsServiceClient->search($customerId, $query);
        $campaign_array = [];
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $campaign_array = array('name'=>$googleAdsRow->getAdGroup()->getName(),'id'=>$googleAdsRow->getAdGroup()->getId());
        }
        return $campaign_array;                
    }
    public function ListKeywordPositionEstimates(GoogleAdsClient $googleAdsClient, int $customerId, int $adGroupId) 
    {
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $googleAdsAdgroupClient = $googleAdsClient->getAdGroupServiceClient();
		// ad_group_criterion.ad_group = "'.$adGroupId.'" AND 
        $query = 'SELECT ad_group_criterion.position_estimates.first_page_cpc_micros, ad_group_criterion.position_estimates.first_position_cpc_micros, ad_group_criterion.position_estimates.top_of_page_cpc_micros, ad_group_criterion.criterion_id, ad_group_criterion.display_name, ad_group_criterion.ad_group, ad_group_criterion.status FROM ad_group_criterion WHERE ad_group.id =  "'.$adGroupId.'" AND ad_group_criterion.type = "KEYWORD" AND ad_group_criterion.status="ENABLED" AND ad_group_criterion.negative="FALSE" AND campaign.status="ENABLED" ORDER BY ad_group_criterion.display_name ASC ';
        $stream =  $googleAdsServiceClient->search($customerId, $query);
        $criterion_array = [];
        $adgroupids = [];
        $adgroups = [];
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            if(!in_array($googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id'], $adgroupids))
            {
               $adgroups[$googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id']] = $this->GetAdgroupName($googleAdsClient, $customerId, $googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id']);
               $adgroupids[] = $googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id'];
            }
			//$adgroups = json_encode($googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup()));
            $criterion_array[$adgroups[$googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id']]['id']][] = array('id'=>$googleAdsRow->getAdGroupCriterion()->getCriterionId(), 'name'=>$googleAdsRow->getAdGroupCriterion()->getDisplayName(), 'status'=>$googleAdsRow->getAdGroupCriterion()->getStatus(), 'adgroup_name'=>$adgroups[$googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id']]['name'], 'adgroup_id'=>$adgroups[$googleAdsAdgroupClient->parseName($googleAdsRow->getAdGroupCriterion()->getAdGroup())['ad_group_id']]['id'], 'first_page_cpc'=>is_null($googleAdsRow->getAdGroupCriterion()->getPositionEstimates())?0:$googleAdsRow->getAdGroupCriterion()->getPositionEstimates()->getFirstPageCpcMicros(), 'first_position_cpc'=>is_null($googleAdsRow->getAdGroupCriterion()->getPositionEstimates())?0:$googleAdsRow->getAdGroupCriterion()->getPositionEstimates()->getFirstPositionCpcMicros(), 'top_page_cpc'=>is_null($googleAdsRow->getAdGroupCriterion()->getPositionEstimates())?0:$googleAdsRow->getAdGroupCriterion()->getPositionEstimates()->getTopOfPageCpcMicros());
        }
        return $criterion_array;                
    }
    
    public function AdowrdsReportDownload(GoogleAdsClient $googleAdsClient, int $customerId,  string $start, string $end) 
    {
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $query = 'SELECT search_term_view.search_term, ad_group.id, ad_group.name, campaign.id, campaign.name FROM search_term_view WHERE segments.date BETWEEN "'.date('Y-m-d', strtotime($start)).'" AND "'.date('Y-m-d', strtotime($end)).'"';
        $stream =  $googleAdsServiceClient->search($customerId, $query);
        $report_array = [];
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $report_array[] = array('camp_name'=>$googleAdsRow->getCampaign()->getName(), 'camp_id'=>$googleAdsRow->getCampaign()->getId(), 'adg_name'=>$googleAdsRow->getAdGroup()->getName(), 'adg_id'=>$googleAdsRow->getAdGroup()->getId(), 'search'=>$googleAdsRow->getSearchTermView()->getSearchTerm());
        }
        return $report_array;                
    }
    
    public function SendInvitations(GoogleAdsClient $googleAdsClient, int $customerId, int $inviteeId) 
    {             
        try {
        $customerClientLink = new CustomerClientLink([
            'client_customer' => ResourceNames::forCustomer($inviteeId),
            'status' => ManagerLinkStatus::PENDING
        ]);

        $customerClientLinkOperation = new CustomerClientLinkOperation();
        $customerClientLinkOperation->setCreate($customerClientLink);
        $customerClientLinkServiceClient = $googleAdsClient->getCustomerClientLinkServiceClient();
        $response = $customerClientLinkServiceClient->mutateCustomerClientLink($customerId, $customerClientLinkOperation);
        $customerClientLinkResourceName = $response->getResult()->getResourceName();
        
        $query = "SELECT customer_client_link.manager_link_id FROM customer_client_link WHERE customer_client_link.resource_name = '$customerClientLinkResourceName'";

        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $response = $googleAdsServiceClient->search($customerId, $query);

        $managerLinkId = $response->getIterator()->current()->getCustomerClientLink()->getManagerLinkId();
        $managerLinkResourceName = ResourceNames::forCustomerManagerLink(
            $inviteeId,
            $customerId,
            $managerLinkId
        );

        $customerManagerLink = new CustomerManagerLink();
        $customerManagerLink->setResourceName($managerLinkResourceName);
        $customerManagerLink->setStatus(ManagerLinkStatus::ACTIVE);

        $customerManagerLinkOperation = new CustomerManagerLinkOperation();
        $customerManagerLinkOperation->setUpdate($customerManagerLink);
        $customerManagerLinkOperation->setUpdateMask(
            FieldMasks::allSetFieldsOf($customerManagerLink)
        );

        $customerManagerLinkServiceClient =  $googleAdsClient->getCustomerManagerLinkServiceClient();
        $customerManagerLinkServiceClient->mutateCustomerManagerLink($inviteeId, [$customerManagerLinkOperation]);
        
        return true;
        } catch (GoogleAdsException $googleAdsException) {            
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {                               
                printf("\t%s",  $error->getMessage(),PHP_EOL);
            }            
        } catch (ApiException $apiException) {
            printf(
                "ApiException was thrown with message '%s'.%s",
                $apiException->getMessage(),
                PHP_EOL
            );            
        }
    }
    
    public function KeywordExists(GoogleAdsClient $googleAdsClient, int $customerId, int $adgroupid, string $keyword)
    {
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $query = 'SELECT ad_group.id, ad_group_criterion.criterion_id, ad_group_criterion.keyword.text, ad_group_criterion.negative FROM ad_group_criterion WHERE ad_group_criterion.type = KEYWORD AND ad_group.id = '.$adgroupid.' AND ad_group_criterion.keyword.text="'.$keyword.'"';
        try{
            $result = 0;        
            $response = $googleAdsServiceClient->search($customerId, $query);
            foreach ($response->iterateAllElements() as $googleAdsRow) {                        
            $result = array('id' => $googleAdsRow->getAdGroupCriterion()->getCriterionId(), 'text' => $googleAdsRow->getAdGroupCriterion()->getKeyword()->getText(), 'negative' => intval($googleAdsRow->getAdGroupCriterion()->getNegative()), 'adgid' => $googleAdsRow->getAdGroup()->getId());
            }
            return $result;
        } catch (GoogleAdsException $googleAdsException) {            
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {                               
                printf("\t%s",  $error->getMessage(),PHP_EOL);
            }            
        } catch (ApiException $apiException) {
            printf(
                "ApiException was thrown with message '%s'.%s",
                $apiException->getMessage(),
                PHP_EOL
            );            
        }           
    }
    
    public function KeywordPositiveExists(GoogleAdsClient $googleAdsClient, int $customerId, int $adgroupid, string $keyword)
    {
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        $query = 'SELECT ad_group.id, ad_group_criterion.criterion_id, ad_group_criterion.keyword.text, ad_group_criterion.keyword.match_type FROM ad_group_criterion WHERE ad_group_criterion.type = KEYWORD AND ad_group.id = '.$adgroupid.' AND ad_group_criterion.keyword.text="'.$keyword.'"';
        try{
            $result = 0;        
            $response = $googleAdsServiceClient->search($customerId, $query);
            foreach ($response->iterateAllElements() as $googleAdsRow) {                        
            $result = array('id' => $googleAdsRow->getAdGroupCriterion()->getCriterionId(), 'text' => $googleAdsRow->getAdGroupCriterion()->getKeyword()->getText(), 'adgid' => $googleAdsRow->getAdGroup()->getId());
            }
            return $result;
        } catch (GoogleAdsException $googleAdsException) {            
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {                               
                printf("\t%s",  $error->getMessage(),PHP_EOL);
            }            
        } catch (ApiException $apiException) {
            printf(
                "ApiException was thrown with message '%s'.%s",
                $apiException->getMessage(),
                PHP_EOL
            );            
        }           
    }
    
    public function AddNegativeKeyword(GoogleAdsClient $googleAdsClient, int $customerId, int $adgroupid, string $keyword)
    {
        $exists = $this->KeywordExists($googleAdsClient, $customerId, $adgroupid, $keyword);
        if(is_array($exists))
        {
            if($exists['negative']==1)
            {
                //echo 'Keyword added once';
				//print_r($exists['id']);
                return $exists['id'];
                //return true;
            }
            else
            {
				//print_r($exists['id']);
                $this->RemoveExistingKeyword($googleAdsClient, $customerId, $adgroupid, $exists['id']);
                return $exists['id'];
            }
        }
        else
        {
            $keywordInfo = new KeywordInfo([
                'text' => $keyword,
                'match_type' => KeywordMatchType::EXACT
            ]);

            $adGroupCriterion = new AdGroupCriterion([
                'ad_group' => ResourceNames::forAdGroup($customerId, $adgroupid),
                'status' => AdGroupCriterionStatus::ENABLED,
                'negative' => true,
                'keyword' => $keywordInfo
            ]);

			$adGroupCriterionOperation = new AdGroupCriterionOperation();
			$adGroupCriterionOperation->setCreate($adGroupCriterion);

			
			$adGroupCriterionServiceClient = $googleAdsClient->getAdGroupCriterionServiceClient();
			try{
			$adGroupCriterionServiceClient->mutateAdGroupCriteria($customerId, [$adGroupCriterionOperation]);
			} catch (GoogleAdsException $googleAdsException) {            
				foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {                               
					printf("\t%s",  $error->getMessage(),PHP_EOL);
				}            
			} catch (ApiException $apiException) {
				printf(
					"ApiException was thrown with message '%s'.%s",
					$apiException->getMessage(),
					PHP_EOL
				);            
			}
			$exists = $this->KeywordExists($googleAdsClient, $customerId, $adgroupid, $keyword);
			return $exists['id'];
        }
        //return true;
    }
	
    public function RemoveNegativeKeyword(GoogleAdsClient $googleAdsClient, int $customerId, int $adgroupid, string $keyword)
    {
        $exists = $this->KeywordExists($googleAdsClient, $customerId, $adgroupid, $keyword);
        if(is_array($exists))
        {
            $this->RemoveExistingKeyword($googleAdsClient, $customerId, $adgroupid, $exists['id']);
			//print_r($exists['id']);
			return $exists['id'];       
        }
        else         
        {
            echo 'Keyword Not Found.';
        }
        //return true;
    }
    
    public function RemoveExistingKeyword(GoogleAdsClient $googleAdsClient, int $customerId, int $adGroupId, int $criterionId)
    {
        $adGroupCriterionResourceName = ResourceNames::forAdGroupCriterion($customerId, $adGroupId, $criterionId);
        $adGroupCriterionOperation = new AdGroupCriterionOperation();
        $adGroupCriterionOperation->setRemove($adGroupCriterionResourceName);

        $adGroupCriterionServiceClient = $googleAdsClient->getAdGroupCriterionServiceClient();
        try{
        $adGroupCriterionServiceClient->mutateAdGroupCriteria($customerId, [$adGroupCriterionOperation]);
        } catch (GoogleAdsException $googleAdsException) {            
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {                               
                printf("\t%s",  $error->getMessage(),PHP_EOL);
            }            
        } catch (ApiException $apiException) {
            printf(
                "ApiException was thrown with message '%s'.%s",
                $apiException->getMessage(),
                PHP_EOL
            );            
        }
        return true;
    }
    
    public function AddPositiveKeyword(GoogleAdsClient $googleAdsClient, int $customerId, int $adgroupid, string $keyword)
    {
			$keywordInfo = new KeywordInfo([
                'text' => $keyword,
                'match_type' => KeywordMatchType::EXACT
            ]);

            $adGroupCriterion = new AdGroupCriterion([
                'ad_group' => ResourceNames::forAdGroup($customerId, $adgroupid),
                'status' => AdGroupCriterionStatus::ENABLED,
                'keyword' => $keywordInfo
            ]);

			$adGroupCriterionOperation = new AdGroupCriterionOperation();
			$adGroupCriterionOperation->setCreate($adGroupCriterion);

			
			$adGroupCriterionServiceClient = $googleAdsClient->getAdGroupCriterionServiceClient();
			try{
			$adGroupCriterionServiceClient->mutateAdGroupCriteria($customerId, [$adGroupCriterionOperation]);
			} catch (GoogleAdsException $googleAdsException) {            
				foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {                               
					printf("\t%s",  $error->getMessage(),PHP_EOL);
				}            
			} catch (ApiException $apiException) {
				printf(
					"ApiException was thrown with message '%s'.%s",
					$apiException->getMessage(),
					PHP_EOL
				);            
			}
			$exists = $this->KeywordPositiveExists($googleAdsClient, $customerId, $adgroupid, $keyword);
			return $exists['id'];
    }
	
    public function AdGroupBid(GoogleAdsClient $googleAdsClient, int $customerId, int $adGroupId, float $bidModifierValue)
	{
        // Creates an ad group bid modifier for mobile devices with the specified ad group ID and
        // bid modifier value.
        $adGroupBidModifier = new AdGroupBidModifier([
            'ad_group' => ResourceNames::forAdGroup($customerId, $adGroupId),
            'bid_modifier' => $bidModifierValue,
            'device' => new DeviceInfo(['type' => Device::MOBILE])
        ]);

        // Creates an ad group bid modifier operation for creating an ad group bid modifier.
        $adGroupBidModifierOperation = new AdGroupBidModifierOperation();
        $adGroupBidModifierOperation->setCreate($adGroupBidModifier);

        // Issues a mutate request to add the ad group bid modifier.
        $adGroupBidModifierServiceClient = $googleAdsClient->getAdGroupBidModifierServiceClient();
        $response = $adGroupBidModifierServiceClient->mutateAdGroupBidModifiers(
            $customerId,
            [$adGroupBidModifierOperation]
        );

        printf("Added %d ad group bid modifier:%s", $response->getResults()->count(), PHP_EOL);

        foreach ($response->getResults() as $addedAdGroupBidModifier) {
            /** @var AdGroupBidModifier $addedAdGroupBidModifier */
            print $addedAdGroupBidModifier->getResourceName() . PHP_EOL;
        }
    }
	
    public function UpdateKeywordBid(GoogleAdsClient $googleAdsClient, int $customerId, int $adGroupId, int $criterionId, int $bidModifierAmount)
	{
        // Creates an ad group criterion with the proper resource name and any other changes.
        $adGroupCriterion = new AdGroupCriterion([
            'resource_name' => ResourceNames::forAdGroupCriterion($customerId, $adGroupId, $criterionId),
            'cpc_bid_micros' => $bidModifierAmount
            //'status' => AdGroupCriterionStatus::ENABLED
			
        ]);

        // Constructs an operation that will update the ad group criterion, using the FieldMasks
        // utility to derive the update mask. This mask tells the Google Ads API which attributes of
        // the ad group criterion you want to change.
        $adGroupCriterionOperation = new AdGroupCriterionOperation();
        $adGroupCriterionOperation->setUpdate($adGroupCriterion);
        $adGroupCriterionOperation->setUpdateMask(FieldMasks::allSetFieldsOf($adGroupCriterion));

        // Issues a mutate request to update the ad group criterion.
        $adGroupCriterionServiceClient = $googleAdsClient->getAdGroupCriterionServiceClient();
        $response = $adGroupCriterionServiceClient->mutateAdGroupCriteria(
            $customerId,
            [$adGroupCriterionOperation]
        );

        // Prints the resource name of the updated ad group criterion.
        /** @var AdGroupCriterion $updatedAdGroupCriterion */
        $updatedAdGroupCriterion = $response->getResults()[0];
       //$updatedAdGroupCriterion = $response->getResults()[0];
        /*printf(
            "Here Updated ad group criterion with resource name: '%s'%s",
            $updatedAdGroupCriterion->getResourceName(),
            PHP_EOL
        );*/
    }
	
     public function KeywordMetrics(GoogleAdsClient $googleAdsClient, int $customerId,  array $locationIds, int $languageId, array $keywords, ?string $pageUrl) 
    {
        $keywordPlanIdeaServiceClient = $googleAdsClient->getKeywordPlanIdeaServiceClient();
        if (empty($keywords) && is_null($pageUrl)) {
            throw new \InvalidArgumentException(
                'At least one of keywords or page URL is required, but neither was specified.'
            );
        }
        $requestOptionalArgs = [];
        if (empty($keywords)) {
            $requestOptionalArgs['urlSeed'] = new UrlSeed(['url' => $pageUrl]);
        } elseif (is_null($pageUrl)) {
            $requestOptionalArgs['keywordSeed'] = new KeywordSeed(['keywords' => $keywords]);
        } else {
            $requestOptionalArgs['keywordAndUrlSeed'] =
                new KeywordAndUrlSeed(['url' => $pageUrl, 'keywords' => $keywords]);
        }
        $geoTargetConstants =  array_map(function ($locationId) {
            return ResourceNames::forGeoTargetConstant($locationId);
        }, $locationIds);

        $response = $keywordPlanIdeaServiceClient->generateKeywordIdeas(
            [
                'language' => ResourceNames::forLanguageConstant($languageId),
                'customerId' => $customerId,
                'geoTargetConstants' => $geoTargetConstants,
                'keywordPlanNetwork' => KeywordPlanNetwork::GOOGLE_SEARCH_AND_PARTNERS
            ] + $requestOptionalArgs
        );
		$return  = [];
        foreach ($response->iterateAllElements() as $result) {
			$return[] = array('text' => $result->getText(), 'avg_monthly_searches'=>is_null($result->getKeywordIdeaMetrics()) ? 0 : $result->getKeywordIdeaMetrics()->getAvgMonthlySearches(), 'competition'=>KeywordPlanCompetitionLevel::name(is_null($result->getKeywordIdeaMetrics()) ? 0 : $result->getKeywordIdeaMetrics()->getCompetition()), 'competition_index'=>is_null($result->getKeywordIdeaMetrics()) ? 0 : $result->getKeywordIdeaMetrics()->getCompetitionIndex(), 'low_top_of_page_bid_micros'=>is_null($result->getKeywordIdeaMetrics()) ? 0 : $result->getKeywordIdeaMetrics()->getLowTopOfPageBidMicros(), 'high_top_of_page_bid_micros'=>is_null($result->getKeywordIdeaMetrics()) ? 0 : $result->getKeywordIdeaMetrics()->getHighTopOfPageBidMicros());
        }
        return $return;
    }
	
}