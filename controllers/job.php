<?php
/**
* 
*/
class Job extends MY_Controller
{
	private $serverID = '1';
	
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('job_scheduler');
		$this->load->helper('array');	//This model loads the categories locally
	}
	

	//Add the first job to add the others in turn..
	function add()	
	{
		$new_object->{'foo'} = 'Bar';
		$this->job_scheduler->add('job/test','index',$new_object);
	}

	

	function get()
	{

		if($this->job_scheduler->get_job_scheduler_status())
		{
			$data = $this->job_scheduler->get_next_job($this->serverID);
			
			if($data)
			{
				if(isset($data->params) && $data->params !=='')
				{
					$params = __unserialize($data->params);
				}else{
					$params = array();
				}
							
				$module_path = rtrim($data->module_path,"/");
				$pieces = explode('/',$module_path);	
				$module_name = end($pieces);
				
				if(isset($data->module_method) && $data->module_method !=='')
				{
					$module_method = $data->module_method;
				}else{
					$module_method = 'index';
				}
				
				//Load the requested Module
				$this->load->module($module_path);
				
				//Check if the module & method exists
				if((int)method_exists($module_name, $module_method) ==1)
				{
					
					//If the job is on the first cycle, and the job status is 0 then set the start time/date
					if($data->status ==0)
					{
						//Set the job details to start
						$this->job_scheduler->start($data->id);
					}
					
					//Start processign the job, and get the return 
					$return = $this->$module_name->$module_method($params,$data->status);
					
					//End the job and set as complete
					if($return == 'complete')
					{
						$this->job_scheduler->complete($data->id);
					}elseif($return == 'error_count')
					{
						$this->job_scheduler->error_count($data->id);
					}elseif($return == 'error')
					{
						$this->job_scheduler->error($data->id);
					}
					
					
				}else{
					//If it doesnt exist then mark it as a error
					log_message('error', 'Job Schedule: Cant find module/method');
					//Set the job to be error
					$this->job_scheduler->error($data->id,'Cant find module/method');
				}
			}else{
				echo "NO JOBS IN QUEUE";
			}
		}else{
			//We are paused at the moment, check database for time when we start again!
			echo "We are paused at the moment, check database for time when we start again! \n";
		}
		
		
	}
}
