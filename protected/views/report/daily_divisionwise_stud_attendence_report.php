
<?php
$this->breadcrumbs=array('Report',
	'Daily Divisionwise Student Attendence Report',
	);

// remove this comment if you want export data through excel.
$this->menu[]=	array('label'=>'', 'url'=>array('DailyDivisionwiseStudentAttendenceReport','excel'=>'excel','lecture_date'=>$date,'branch_id'=>$branch,'acdm_name_id'=>$sem,'lecture'=>$lecture,'sid'=>$sid),'linkOptions'=>array('class'=>'export-excel','title'=>'Export to Excel','target'=>'_blank'));


?>
<div class="portlet box blue">
<i class="icon-reorder"></i>
 <div class="portlet-title"><span class="box-title">Select Criterias</span>
</div>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'student-form',
	'enableAjaxValidation'=>true,

)); ?>

<?php 

	$period=array();
        $faculty=array();
	$batch=array();		
	$cols=array();

	$org_id=Yii::app()->user->getState('org_id');
	
	$acd = Yii::app()->db->createCommand()
		->select('*')
		->from('academic_term')
		->where('current_sem=1 ')
		->queryAll();
	$acdterm=CHtml::listData($acd,'academic_term_id','academic_term_name');	
	$period=array();
	if($acdterm)
	{
	$pe_data = AcademicTermPeriod::model()->findByPk($acd[0]['academic_term_period_id']);
	$period[$pe_data->academic_terms_period_id] = $pe_data->academic_term_period; 
	}
 
?>
	<div class="row">        
		<?php echo $form->labelEx($model,'acdm_period_id'); ?>
		<?php echo $form->dropDownList($model,'acdm_period_id',$period,array('tabindex'=>1,'prompt' => 'Select Year')); ?>
		<span class="status">&nbsp;</span>
		<?php echo $form->error($model,'acdm_period_id'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'branch_id'); ?>
		<?php echo $form->dropDownList($model,'branch_id', CHtml::listData(Branch::model()->findAll(),'branch_id','branch_name'), array(
			'tabindex'=>2,'empty' => 'Select Branch','options' =>array($branch=>array('selected'=>true)),
			'ajax' => array(
			'type'=>'POST', 
			'url'=>CController::createUrl('dependent/getattendancediv'), 
			'update'=>'#Attendence_div_id', //selector to update
			))); ?><span class="status">&nbsp;</span>
		<?php echo $form->error($model,'branch_id'); ?>		
	 </div>
	<div class="row">
		<?php echo $form->labelEx($model,'acdm_name_id'); ?>
		<?php echo $form->dropDownList($model,'acdm_name_id',$acdterm,array('tabindex'=>3,'empty' => 'Select Semester','options' =>array($sem=>array('selected'=>true))));  ?>
		<span class="status">&nbsp;</span>
		<?php echo $form->error($model,'acdm_name_id'); ?>
	</div>
	<div class="row">
	<?php echo CHtml::label(Yii::t('demo', 'Date'), 'date'); ?>
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker',
		    array(
			    'model'=>$model,
			    'attribute'=>'lecture_date',
			    'language' => 'en',
			    'options' => array(
			    'dateFormat'=>'dd-mm-yy',
			    'changeMonth' => 'true',
			    'changeYear' => 'true',
			    'duration'=>'fast',
			    'showAnim' =>'slide',
			    'yearRange'=>'1900:'.date('Y'),
			),
			'htmlOptions'=>array(
			'style'=>'height:18px;
			    padding-left: 4px;width:168px;'

			)
		    )
		);
	?>
	</div>   

	<div class="row buttons">
	<?php echo CHtml::submitButton('Search', array('class'=>'submit','name'=>'search')); ?>
	</div>


<?php $this->endWidget(); ?>
</div>
</div>

<?php
if(isset($date))
{

 $org = Organization::model()->findAll();
	$org_data=$org[0];
 $branch_model=Branch::model()->findByPk($branch);
 $acd_model = AcademicTerm::model()->findByPk($sem);
 $subject=array();
 $faculty=array();	
?>
	<div class="portlet box yellow" style="width:100%;margin-top:20px;">
  	  <i class="icon-reorder"></i>
  	  <div class="portlet-title"><span class="box-title">Document Search Details</span>
  	  	<div class="operation">
		  <?php echo CHtml::link('Back', array('Report/DailyDivisionwiseStudentAttendenceReport'), array('class'=>'btnback'));?>	
		 <?php echo CHtml::link('Excel', array('DailyDivisionwiseStudentAttendenceReport','excel'=>'excel','lecture_date'=>$date,'branch_id'=>$branch,'acdm_name_id'=>$sem,'lecture'=>$lecture,'sid'=>$sid), array('class'=>'btnblue'));?>	
		</div>
  	  </div>
	<div class="portlet-body" >
	<?php	echo '<table class="report-table" border="2" > ';
	echo "<thead>";
	echo "<tr align=center> <th  colspan = 60 style=text-align:left;> ".CHtml::image(Yii::app()->controller->createUrl('/site/loadImage', array('id'=>$org_data->organization_id)),'No Image',array('width'=>80,'height'=>55,'style'=>'float:left;margin-left:200px;')) ."
	 <big> <b>".$org_data->organization_name ."</big><br>". $org_data->address_line1." ".$org_data->address_line2."</br>"  . City::model()->findBypk($org_data->city)->city_name.", ".State::model()->findBypk($org_data->state)->state_name.", ".Country::model()->findBypk($org_data->country)->name."." ." </th> <br>	 </tr>";
	echo "<tr><th colspan=16><h3> Branch  of ".$branch_model->branch_name." Semester-".$acd_model->academic_term_name." Students  Attendence of Date-".date("d-m-Y", strtotime($date))." Report</h3></th> </tr>";

	echo "<tr><th colspan=2><b> Academic Year </b></th> <th colspan = 50 ><b> ".$pe_data->academic_term_period."</b></th></tr>";
	
	echo "<tr class='table_header'>"; 
	echo "<th rowspan=7>SI No.</th>";
	echo "<th rowspan=7> Enroll No.</th>";
	echo "<th rowspan=7 >Student Name</th>";
	echo "<th colspan=16> No. Of Lectures </th></tr><tr>"; 
	$j=1;
	$lec_hour=1;
	$comb_subject=array();	
	for($i=1;$i<=$lecture;$i++)
	{
		$check_timetable=TimeTableDetail::model()->findAll(
		array('condition'=>'acdm_name_id='.$sem.' and branch_id='.$branch.' and lecture_date="'.$date.'" and lec='.$i.' and proxy_status <> 1 order by lec' ));
		
		$f=0;	
		if(empty($check_timetable)) 
		{
			echo "<th >$i</th>";
			$cols[]=1;
			$subject[]=0;
			$batch[]=0;
			$faculty[]=0;
			$lectures[]=0;
			$comb_subject[]=0;
			$j++;
		}
		else
		{
		$timetable = TimeTable::model()->findByPk($check_timetable[0]->timetable_id);
		$lec_dur =  LecDuration::model()->findAll(array('condition'=>'timetable_id='.$timetable['timetable_id']));
		
		$comb_subject[]=count($check_timetable);
		
		foreach($check_timetable as $list) 
		{
			$batchid=$list->batch_id;
			$rows = 2;			
			$cols[]=$list['lect_hour'];
			$lectures[]=$list->lec;
			$subject[]=$list->subject_id;
			$faculty[]=$list->faculty_emp_id;
			$batch[]=$list->batch_id;
		}
				for($k=1;$k<=$list['lect_hour'];$k++)
		{
			echo "<th>". $i."</th>";	
			$i++;				
		}
		$i--;
	}		
   }
	echo "</tr><tr>";
	$cs=0;
	for($sub=0;$sub<count($subject);$sub++)
	{
		if($subject[$sub]!=0)
		{
			if($cols[$sub]>1)
			{ 
			   $count=$comb_subject[$cs];
			   $cols_sub=$cols[$sub];			
			   print "<th colspan=".$cols_sub."><table border='1'><tr>";	
			   for($c=0;$c<$count;$c++)	
			   {				
			   	print '<th>'.SubjectMaster::model()->findByPk($subject[$sub])->subject_master_name."</th>";
				$sub++;	
	 		   }	
			   print "</tr></table></th>";
			   $sub--;				
			}			
			else
			{					
			print '<th>'.SubjectMaster::model()->findByPk($subject[$sub])->subject_master_name.' </th>';
			}			
		}
	  	else 
		{
			print '<th >-</th>';
		}
		$cs++;
	}
	echo "</tr><tr>"; 
	$cs=0;
	for($fac=0;$fac<count($faculty);$fac++)
	{
		if($faculty[$fac]!=0)
		{
			if($cols[$fac]>1)
			{
			   $count=$comb_subject[$cs];	
			   $cols_fac=$cols[$fac];
			   print "<th colspan=".$cols_fac."'>";	
			   $t = $count - 1;
			   for($c=0;$c<$count;$c++)	
			   {
				if($c == $t)
	                	   	print ' <div  style="float: left; padding-right: 5px; margin-right: 5px;  width='.((100/$count)-14).'%;">';
				else			
			        	print ' <div style="border-right:thin solid #74B9F0; float: left;  width:'.((100/$count)-14).'%; padding-right: 5px; margin-right: 5px;">';
				$fname=EmployeeInfo::model()->findByAttributes(array('employee_info_transaction_id'=>$faculty[$fac]));		

				print $fname->employee_first_name.' '.$fname->employee_last_name;
				print '</div>';
				$fac++;
			   }		
			   print "</th>";
			   $fac--;		  
			 }	
			 else
			 {
				$fname=EmployeeInfo::model()->findByAttributes(array('employee_info_transaction_id'=>$faculty[$fac]));		
		print '<th>'.$fname->employee_first_name.' '.$fname->employee_last_name.'</th>';
			 }
		}
	  	else 
			print '<th>-</th>';
		$cs++;
	}	
	print '<tr> </tr>';	
	$cs=0;
	for($b=0;$b<count($batch);$b++)
	{		
		if($batch[$b]!=0)
		{
			if($cols[$b]>1)
			{
			   $count=$comb_subject[$cs];	
			   $cols_b=$cols[$b];		  
			   print "<th colspan=".$cols_b.">";
		           $t = $count - 1;	
			    for($j=0;$j<$count;$j++)
			    {
				if($j == $t)
				  print ' <div  style=" padding-right: 5px; margin-right: 3px;  width='.((100/$count)-14).'%;">';
				else			
				  print ' <div style="border-right:thin solid #74B9F0; float: left;  width:'.((100/$count)-14).'%; padding-right: 5px; margin-right: 10px;">';
				print Batch::model()->findByPk($batch[$b])->batch_code;
				print '</div>';
				$b++;	
		 	     }
			     $b--;
			}
			else
			{
			   print '<th>'.Batch::model()->findByPk($batch[$b])->batch_code.' </th>';
			}
		}
	  	else 
			print '<th >-</th>';
		$cs++;
	}
	echo "</tr> <tr>";
	echo "</thead>";
	$m=0;

     foreach($sid as $s)
     {
	 if(($m%2) == 0)
	 {
		  $class = "odd";
	 }
	 else
	 {
	        $class = "even";
	 }	
	echo '<tr class='.$class.'>';
	echo '<td>';
		echo ++$m;
	echo '</td>';
	$stud_tran=StudentTransaction::model()->findByPk($s);	
	$stud_name=StudentInfo::model()->findByAttributes(array('student_info_transaction_id'=>$s));		
	$batch='';
	echo '<td>';
		if(!empty($stud_name->student_enroll_no))	
    			echo $stud_name->student_enroll_no;
		else
			echo 'Not Set';
	echo '</td>';
	echo '<td >';
		if($stud_tran->student_transaction_batch_id!=0)
	$batch=	Batch::model()->findByPk($stud_tran->student_transaction_batch_id)->batch_code;		
	echo $stud_name->student_first_name.' '.$stud_name->student_last_name.' ( '.$stud_name->student_roll_no.' )'.'('.$batch.')';	
	echo '</td>';
	$cs=0;	
	for($l=0;$l<count($lectures);$l++)
	{	
	  if($lectures[$l]!=0)
	  {
	    if($cols[$l]>1)
	    {
	       $count=$comb_subject[$cs];	
	       $cols_l=$cols[$l];		  
	       print "<th colspan=".$cols_b.">";
	       $t = $count - 1;	
	       for($j=0;$j<$count;$j++)
	       {
		 if($j == $t)
		     print ' <div  style=" padding-right: 5px; margin-right: 3px;  width='.((100/$count)-14).'%;">';
		 else			
		     print ' <div style="border-right:thin solid #74B9F0; float: left;  width:'.((100/$count)-14).'%; padding-right: 5px; margin-right: 10px;">';
                $result1=Attendence::model()->findByAttributes(array('st_id'=>		$s,'attendence_date'=>"$date",'student_attendence_period_id'=>$lectures[$l],'sub_id'=>$subject[$l],'employee_id'=>$faculty[$l]));
		if(count($result1) !=0)		
		{
		  $myclass = $result1->attendence == 'P' ? 'green' : 'red';
		  print "<b style=color:$myclass>".$result1->attendence."</b>";
		}	
		else
		  print "  -  "; 
		print '</div>';
		$l++;
		}	  
		$l--;		
	      }
	      else 
	      {
		   $result1=Attendence::model()->findByAttributes(array('st_id'=>$s,'attendence_date'=>"$date",'student_attendence_period_id'=>$lectures[$l],'employee_id'=>$faculty[$l]));
		if(count($result1) !=0)		
		{						  	
			$myclass = $result1->attendence == 'P' ? 'green' : 'red'; 
			print "<td><b style=color:$myclass>".$result1->attendence."</b></td>";
		}	
		else
			print "<td> - </td>"; 
		
	      }
        }	
      else 
      {
		print "<td> - </td>"; 
      }
      $cs++;		
     }
	echo '</tr>';
   } 	
	echo "</table>";
}
?>
</div>
</div>
