<?php

/**
 * Copyright (c) 2016 by Hinderling Volkart AG
 * All rights reserved
 *
 * http://www.hinderlingvolkart.com/
 *
 * Ronny Binder <rbi@hinderlingvolkart.com>
 *
 */

namespace Contao;


class DC_TableMetaModelsArticle extends DC_Table
{
	protected function parentView()
	{
		return preg_replace(
			[
				// "Edit parent" Button
				'#<div class="tl_header [^>]*>\n<div class="tl_content_right">\n<a #',
				// Parent entry info
				'#<td><span class="tl_label">tstamp:</span>.*\n.*</td>#',
			],
			[
				'$0style="display:none" ',
				'<td>&nbsp;</td>',
			],
			parent::parentView()
		);
	}
}
