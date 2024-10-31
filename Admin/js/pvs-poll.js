const getData = async (url = '', data = {}) => {
	// Default options are marked with *
	const response = await fetch(url, {
		// headers: {
		//   "Content-Type": "application/json",
		// },
		credentials: 'same-origin',
		method: 'POST', // *GET, POST, PUT, DELETE, etc.
		body: data, // body data type must match "Content-Type" header
	});
	const responseData = await response.json(); // parses JSON response into native JavaScript objects

	return responseData;
};

/**
 * Post data method.
 * @param {url} url api url
 * @param {method} method request type
 * @returns
 */
const postData = async (url = '', data = {}) => {
	// Default options are marked with *
	const response = await fetch(url, {
		headers: {
			// 'Content-Type': 'application/json',
			// 'Content-Type': 'application/x-www-form-urlencoded',
		},
		credentials: 'same-origin',
		method: 'POST', // *GET, POST, PUT, DELETE, etc.
		body: data, // body data type must match "Content-Type" header
	});
	const responseData = await response.json(); // parses JSON response into native JavaScript objects

	return responseData;
};

function validateAnswer ( answer ) {

	try {
		let janswer = JSON.stringify( answer );
        JSON.parse(janswer);
		return true;

    } catch (e) {
        return false;
    }
}
function submitVote(answer, totalvotes, current_answer_id ) {

	if( ! Number.isInteger( totalvotes ) ){
		throw new Error('Invalid totalvotes')
	} else if( ! Number.isInteger( current_answer_id ) ) {
		throw new Error( 'Invalid answer id')
	} else if ( ! validateAnswer( answer ) ) {
		throw new Error( 'Invalid answer')
	}

	if( current_answer_id ) {
		alert('You already particated.');
		return
	}

	if( ! confirm('Are you sure? You can participate only once.')){
		return;
	}


	let form = new FormData();
	form.append('nonce', pvs.nonce);
	form.append('pvs_qid', answer.pvs_qid);
	form.append('pvs_aid', answer.pvs_aid);
	form.append('pvs_votes', answer.pvs_votes);
	form.append('totalvotes', totalvotes);
	form.append('action', 'give_vote');
	postData(pvs.ajax_url, form).then((res) => {
		if (res.data) {
			alert('Your vote is saved.');
		}
	});
}