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

trait Redirecting
{
	/**
	 * Redirect to target media site version with path and query string.
	 * @param string $targetMediaSiteVersion 
	 * @return bool
	 */
	protected function redirectToTargetMediaSiteVersion ($targetMediaSiteVersion) {
		return $this->redirectToTargetVersion($targetMediaSiteVersion, $this->requestLocalization);
	}

	/**
	 * Redirect to target localization version with path and query string.
	 * @param \string[] $targetLocalization 
	 * @return bool
	 */
	protected function redirectToTargetLocalization ($targetLocalization) {
		return $this->redirectToTargetVersion($this->requestMediaSiteVersion, $targetLocalization);
	}

	/**
	 * Redirect to target media site version and localization version with path and query string.
	 * @param string $targetMediaSiteVersion 
	 * @param \string[] $targetLocalization 
	 * @return bool
	 */
	protected function redirectToTargetVersion ($targetMediaSiteVersion, $targetLocalization) {
		// unset site key switch param and redirect to no switch param uri version
		$request = & $this->request;
		$mediaVersionUrlParam = static::MEDIA_VERSION_URL_PARAM;
		$localizationUrlParam = static::LANG_AND_LOCALE_SEPARATOR;
		$targetLocalizationStr = implode($localizationUrlParam, $targetLocalization);
		$targetIsTheSameAsDefault = $targetLocalizationStr === $this->defaultLocalizationStr;
		if ($this->anyRoutesConfigured) {
			$path = $request->GetPath(TRUE);
			$targetLocalizationStr = ($targetIsTheSameAsDefault && ($path == '/' || $path == ''))
				? ''
				: '/' . $targetLocalizationStr;
			$targetUrl = $request->GetBaseUrl()
				. $this->allowedSiteKeysAndUrlPrefixes[$targetMediaSiteVersion] 
				. $targetLocalizationStr
				. $path;
		} else {
			$targetUrl = $request->GetBaseUrl();
			if ($targetMediaSiteVersion === static::MEDIA_VERSION_FULL) {
				if (isset($this->requestGlobalGet[$mediaVersionUrlParam]))
					unset($this->requestGlobalGet[$mediaVersionUrlParam]);
			} else {
				$this->requestGlobalGet[$mediaVersionUrlParam] = $targetMediaSiteVersion;
			}
			if ($targetIsTheSameAsDefault) {
				if (isset($this->requestGlobalGet[$localizationUrlParam]))
					unset($this->requestGlobalGet[$localizationUrlParam]);
			} else {
				$this->requestGlobalGet[$localizationUrlParam] = $targetLocalizationStr;
			}
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$targetUrl .= $request->GetScriptName();
		}
		if ($this->requestGlobalGet) {
			$amp = $this->getQueryStringParamsSepatator();
			$targetUrl .= '?' . str_replace('%2F', '/', http_build_query($this->requestGlobalGet, '', $amp));
		}
		$this->redirect($targetUrl, \MvcCore\Interfaces\IResponse::SEE_OTHER);
		return FALSE;
	}
}
