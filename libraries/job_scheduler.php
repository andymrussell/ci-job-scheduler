<?php
/**
* 
*/
class job_scheduler
{
	private $CI;
	private $table_name = 'job_schedule';
	private $job_status = array();
	private $job_server = '1';
	
	function __construct()
	{
		$this->CI = &get_instance();
		$this->CI->load->config('job/job_scheduler');
		if(!$this->CI->config->item('job_status'))
		{
			log_message('error', 'Job Schedule -  No config file found');
			show_error('Job Schedule -  No config file found');
			return FALSE;
		}else{
			$this->job_status = $this->CI->config->item('job_status');
		}
		
		date_default_timezone_set('UTC');
	}
	
	/**
	 * get_job_scheduler_status
	 *
	 * Check if the job scheduler should be running right now.
	 *
	 * @param	int		ServerID
	 * @access	public
	 * @return	int
	 */	
	public function get_job_scheduler_status()
	{
		$this->CI->db->from('job_schedule_status');
		$this->CI->db->where('id','1');
		$query = $this->CI->db->get();
		$result = $query->row();
		
		//If jobs are currently paused
		if($result->live ==0)
		{
			//If the time in the database is less than now, we should start the jobs again! :)
			if($result->start_time  <= time())
			{
				//Start
				$this->CI->db
						->where('id', '1')
						->from('job_schedule_status')
						->set('live', '1')
						->set('start_time', NULL)
						->update();
				return TRUE;
			}else{
				//do nothing.
				return FALSE;
			}
		}else{
			return TRUE;
		}

	}
	
	
	/**
	 * Get Job By ID
	 *
	 * This function returns the latest job by an ID and server.
	 *
	 * @param	int		jobID
	 * @param	int		ServerID
	 * @access	public
	 * @return	int
	 */	
	public function get_job_by_id($id)
	{
		$this->CI->db->from($this->table_name);
		$this->CI->db->where('id', $id);
		$this->CI->db->limit('1');
		$query = $this->CI->db->get();
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else{
			return FALSE;
		}
	}
	
	function get_status_name($statusID)
	{
		if(isset($this->job_status[$statusID]))
		{
			return $this->job_status[$statusID];
		}else{
			return FALSE;
		}
	}
	
	/**
	 * Get Next Job
	 *
	 * This function returns the latest job for this server.
	 *
	 * @param	string	status	 
	 * @param	int		ServerID
	 * @access	public
	 * @return	int
	 */	
	public function get_next_job($serverID=1)
	{
	
		$this->CI->db->from($this->table_name);
		$this->CI->db->where('server_id', $serverID);
		$this->CI->db->where('finished', '0');
		$this->CI->db->where('error', NULL);
		$this->CI->db->order_by('id', 'ASC');
		$this->CI->db->limit('1');
		$query = $this->CI->db->get();
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else{
			return FALSE;
		}
	}
	
	/**
	 * Add Job
	 *
	 * This function returns adds the job to the schedule.
	 *
	 * @param	string	URI path of module
	 * @param	string	Method of the Module
	 * @param	array	Params to pass to job
	 * @param	int		ServerID
	 * @access	public
	 * @return	int
	 */
	public function add($path,$method,$params,$serverID=1)
	{
		$data = array(
			'module_path' 	=> rtrim($path,"/"),
			'module_method'	=> $method,
			'params' 		=> serialize($params),
			'server_id' 	=> $serverID,
			);
		$this->CI->db->insert($this->table_name,$data);
		return $this->CI->db->insert_id();
	}

	/**
	 * Job Status
	 *
	 * This function sets the job custom status.
	 *
	 * @param	int		jobID
	 * @param	string	Job Status
	 * @access	public
	 * @return	void
	 */	
	public function status($id,$status='')
	{
		if($status !== '' && isset($this->job_status[$status]))
		{
			$status = $this->job_status[$status];
		}else{
			$status = '0';
		}
		
		$data = array(
			'status'		=> $status,
			);
		$this->CI->db->where('id', $id);
		$this->CI->db->update($this->table_name, $data); 
	}
	
	/**
	 * Start Job
	 *
	 * This function sets the job status to start.
	 *
	 * @param	int		jobID
	 * @access	public
	 * @return	void
	 */	
	public function start($id)
	{
		$data = array(
			'status'		=> $this->job_status['start'],
			'date_start' 	=> date('Y-m-d H:i:s'),
			);
		$this->CI->db->where('id', $id);
		$this->CI->db->update($this->table_name, $data); 
	}
	
	/**
	 * Complete Job
	 *
	 * This function sets the job status to complete.
	 *
	 * @param	int		jobID
	 * @access	public
	 * @return	void
	 */	
	public function complete($id)
	{
		$data = array(
			'finished'	=> '1',
			'date_end' 	=> date('Y-m-d H:i:s'),
			);
		$this->CI->db->where('id', $id);
		$this->CI->db->update($this->table_name, $data); 
	}

	/**
	 * Error Count
	 *
	 * This function adds an error count to the job.
	 *
	 * @param	int		jobID
	 * @access	public
	 * @return	void
	 */
	public function error_count($id)
	{
		$this->CI->db->set('error_count','error_count+1',false);		
		$this->CI->db->where('id', $id);
		$this->CI->db->update($this->table_name); 
	}

	/**
	 * Set job as Error 
	 *
	 * This function adds an error count to the job.
	 *
	 * @param	int		jobID
	 * @access	public
	 * @return	void
	 */
	function error($id,$error_msg=NULL)
	{
		$data = array(
			'error'		=> '1',
			'error_msg'	=> $error_msg,
			'date_end'	=> date('Y-m-d H:i:s'),
			);
		$this->CI->db->where('id', $id);
		$this->CI->db->update($this->table_name, $data); 
	}	
}
