<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\MediaAndLocalization;

trait RedirectSections
{
	/**
	 * Redirect to target media site version and localization version with path and query string.
	 * @param array $systemParams 
	 * @return bool
	 */
	protected function redirectToVersionSections ($systemParams) {
		$request = & $this->request;
		$urlBaseSection = $request->GetBaseUrl();
		$urlPathWithQuerySection = $request->GetPath(TRUE);
		
		$targetMediaSiteVersion = NULL;
		$targetLocalization = NULL;

		$mediaVersionParamName = \MvcCore\Ext\Routers\IMedia::URL_PARAM_MEDIA_VERSION;
		$localizationParamName = \MvcCore\Ext\Routers\ILocalization::URL_PARAM_LOCALIZATION;
		$this->redirectStatusCode = \MvcCore\IResponse::MOVED_PERMANENTLY;

		if (isset($systemParams[$mediaVersionParamName])) 
			$targetMediaSiteVersion = $systemParams[$mediaVersionParamName];
		if (isset($systemParams[$localizationParamName])) 
			$targetLocalization = $systemParams[$localizationParamName];

		if ($targetMediaSiteVersion === NULL) {
			$this->redirectStatusCode = \MvcCore\IResponse::MOVED_PERMANENTLY;
			$targetMediaSiteVersion = $this->requestMediaSiteVersion !== NULL
				? $this->requestMediaSiteVersion
				: ($this->sessionMediaSiteVersion !== NULL
					? $this->sessionMediaSiteVersion
					: static::MEDIA_VERSION_FULL
				);
		}
		if ($targetLocalization === NULL) {
			$this->redirectStatusCode = \MvcCore\IResponse::SEE_OTHER;
			$targetLocalization = $this->requestLocalization !== NULL
				? $this->requestLocalization
				: ($this->sessionLocalization !== NULL
					? $this->sessionLocalization
					: $this->defaultLocalization
				);
		}

		// unset site key switch param and redirect to no switch param uri version
		$targetMediaUrlValue = $this->redirectMediaGetPrefixAndUnsetGet($targetMediaSiteVersion);
		$targetLocalizationUrlValue = $this->redirectLocalizationGetPrefixAndUnsetGet($targetLocalization);
		
		$urlPathWithQueryIsHome = NULL;
		if ($this->anyRoutesConfigured) {
			if ($targetMediaUrlValue === NULL) 
				unset($systemParams[$mediaVersionParamName]);
			
			if ($targetLocalizationUrlValue === NULL) {
				unset($systemParams[$localizationParamName]);
			} else if ($targetLocalizationUrlValue === $this->defaultLocalizationStr) {
				$urlPathWithQueryIsHome = $this->urlIsHomePath($urlPathWithQuerySection);
				if ($urlPathWithQueryIsHome)
					unset($systemParams[$localizationParamName]);
			}

			$this->redirectAddAllRemainingInGlobalGet($urlPathWithQuerySection);
		} else {
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$urlPathWithQuerySection .= $request->GetScriptName();
			$this->redirectAddAllRemainingInGlobalGet($urlPathWithQuerySection);
		}

		return [
			$urlBaseSection,
			$urlPathWithQuerySection, 
			$systemParams,
			$urlPathWithQueryIsHome
		];
	}
}
