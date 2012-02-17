<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2012 Colomban Wendling <ban@herbesfolles.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */


/* Naive caching */
abstract class Cache
{
	protected $cache_file;
	protected $cache_time;
	
	public function __construct ($cache_file, $cache_time)
	{
		$this->cache_file = $cache_file;
		$this->cache_time = $cache_time;
	}
	
	/* called to fill the cache when the cache file doesn't exist or expired
	 * @returns: the data to load.  false indicates failure */
	protected abstract function fill ();
	
	/* called to serialize the cache data to the cache file
	 * @param data: the data to cache (after filtering)
	 * @returns: the data to save in the cache file.  false indicates failure */
	protected function serialize (&$data)
	{
		return $data;
	}
	
	/* called to unserialize the cache data from the cache file
	 * @param data: the cached data
	 * @returns: the data to load.  false indicates failure */
	protected function unserialize (&$data)
	{
		return $data;
	}
	
	public function load () {
		$data = false;
		$mtime = @filemtime ($this->cache_file);
		
		if (! $mtime || time () - $mtime >= $this->cache_time) {
			/* cache time expired, fetch source */
			if (($data = $this->fill ()) !== false) {
				if (($save_data = $this->serialize ($data)) !== false) {
					file_put_contents ($this->cache_file, $save_data, LOCK_EX);
				}
			}
		} else {
			if (($data = file_get_contents ($this->cache_file)) !== false) {
				$data = $this->unserialize ($data);
			}
		}
		
		return $data;
	}
}
