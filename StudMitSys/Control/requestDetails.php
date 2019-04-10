<?php
require_once '../../vendor/autoload.php';

if (empty($_GET['id'])) { #if the user is trying to get to the page without an ID then redirect to homepage
    header("Location: ");
    exit();
} else {
    require_once 'userFunctions.php';
    require_once '../Model/readDatastore.php';
    $id = $_GET['id'];

    #If not a tutor or the student that made the request then redirect back to home page as only they can access a request, else populate page.
    if (requestOwnerOrTutor($id) == false) {
        header("Location: i7648171.appspot.com");
        exit();
    } else {
        $requestDetailsData = getGQLData('Request', 'select * from Request where __key__ = key(Request, ' . $id . ') order by timeCreated desc'); #Get request data
            if (groupCheck() == 'Tutor') { #If user is tutor then give them option to reassign assigned tutor, else show the tutor name.

                #Build the tutor select drop down
                $tutorNameEmail = '<select id="tutorSelect" onchange="tutorChange()"><option value="Unassigned">Unassigned</option>';
                $tutorList = getGQLData('Account', 'select __key__, Name from Account where role = "Tutor" AND active = true order by Name asc'); #Get list of active tutors
                foreach ($tutorList as $tutorData) { #Option for each active tutor
                    $tutorNameEmail .= '<option value="'. $tutorData->getKeyName() .'"';
                    if ($requestDetailsData[0]->assignedTo == $tutorData->getKeyName()) { #If tutor is the one assigned to request then make their name selected.
                        $tutorNameEmail .= ' selected = "selected"';
                    }
                    $tutorNameEmail .= '>'. $tutorData->Name .'</option>';
                }
                $tutorNameEmail .= '</select>';
            } else { #If student then tutor name with link.
                if ($requestDetailsData[0]->assignedTo !== 'Unassigned') {
                    $tutorNameEmail = '<a href="/userDetails?id=' . $requestDetailsData[0]->assignedTo . '">' . getUserFullName($requestDetailsData[0]->assignedTo) . '</a>';
                } else {
                    $tutorNameEmail = '';
                }
            }
            $userNameEmail = '<a href="/userDetails?id=' . $requestDetailsData[0]->email . '">' . getUserFullName($requestDetailsData[0]->email) . '</a>'; #Get student name with link to profile
            #Get other values from results
            $timeCreated = $requestDetailsData[0]->timeCreated->format("H:i d-m-Y");
            $title = $requestDetailsData[0]->title;
            $status = $requestDetailsData[0]->status;
            $reason = $requestDetailsData[0]->reason;
            $description = $requestDetailsData[0]->description;
            $imageLink = $requestDetailsData[0]->imageLink;

        #If user made the request or is assigned to it then show the close and followup forms.
        if (requestOwnerOrAssigned($id) == true AND $requestDetailsData[0] -> status == 'Open') {
            if (groupCheck() == 'Tutor') { #Only show close reason drop down if a tutor and assigned to request.
                $closeSelect = '<select id="closeReasonSelect"><option>Accepted: Acceptable circumstance</option><option>Denied: Invalid circumstance</option><option>Not Applicable</option><option>Open</option></select>';
            } else {
                $closeSelect ='';
            }
            $requestDetailsFollowupForm = file_get_contents('../View/requestDetailsFollowupForm.html'); #Get followup form
            $requestDetailsCloseForm = file_get_contents('../View/requestDetailsCloseForm.html'); #Get close form, in 2 parts to insert the closeSelect element.
            $requestDetailsCloseForm .= $closeSelect .'<button type="submit" id="submitClose">Close Request</button></form>';
        } else {
            $requestDetailsFollowupForm = '';
            $requestDetailsCloseForm = '';
        }

        #Get data for any follow up comments
        $followupRows = '';
        $requestHistoryData = getGQLData('Followup', 'select * from Followup where __key__ HAS ANCESTOR KEY(Request, ' . $id . ') order by timeCreated desc');
        foreach ($requestHistoryData as $data) {
            if (!empty($data)) {
                $followupRows .= '<tr><td width="26%">' . getUserFullName($data->email) . ' at ' . $data->timeCreated->format("H:i d-m-Y") . '</td><td width="74%">' . $data->followupText . '<br><img class="mediumImg" src="' . $data->imageLink . '"></td></tr>'; #Row for each followup.
            }
        }
        require_once 'smartyFunctions.php';
        smartyHeader(); #Setup smarty with header
    }
}