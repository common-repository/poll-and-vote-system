import React, { useEffect } from 'react';
import { __ } from '@wordpress/i18n'

import { postData} from './block/utilities';
import { Form } from 'react-bootstrap';
const { InspectorControls  } = wp.blockEditor;
const { PanelBody  } = wp.components;


wp.blocks.registerBlockType('pvs/poll', {
	title: __('Poll System'),
	description: __('This is simple poll discription.'),
	icon: 'chart-bar',
	category: 'design',
	keywords: ['poll', 'vote', 'epoll', 'booth', 'wpolls', 'polls'],
	example: {},
	attributes: {
		panelCSS: {
			type: 'object',
			default: {
				item: {
					paddingTop: '20px',
				}
			}
		},
		question: { 
			type: 'string',
			default: "How is my site?"
		 },
		answers: { 
			type: 'array',
			default: ['Good', 'Well', 'Excellent' ],
		 },
		id: {type: 'string'},
		polls: { 
			type: 'array',
			default: []
		},
		customclass: { 
			type: 'string',
			default: ''
		},
		customcss: { 
			type: 'string',
			default: ''
		},
	},

	edit: createPoll,
	save: function (props) {
		return null;
	},
});

function createPoll(props) {

	useEffect(() => {
		let form = new FormData();
		form.append('nonce', pvs.nonce);
		form.append('action', 'get_polls');
		postData(pvs_block.ajax_url, form).then((res) => {
			if (res.data) {
				props.setAttributes({ polls: res.data });
			}
		});

	}, [] )
	const setQuestion = (e) => {
		props.setAttributes({ question: e.target.value });
		props.setAttributes({ answers: ['Yes', 'No'] });
	};

	const selectPoll = (e) => {
		let question = props.attributes.polls.filter(poll=> poll.id === e.target.value ); 
		props.setAttributes({ question: question[0].question });
		let answers = []
		question[0].answers.map(answer=> {
			answers.push( answer.pvs_answers )
		} );
		props.setAttributes({ id: e.target.value });
		props.setAttributes({ answers: answers });

	}
	const setCustomClass = (e) => {
		props.setAttributes({ customclass : e.target.value });
	}
	const setCustomCSS = (e) => {
		props.setAttributes( { customcss: e.target.value } );
	}

	return (
		[
			<InspectorControls >
				<PanelBody >
					<Form>
						<Form.Group style={props.attributes.panelCSS.item}>
							<Form.Label>{__('All Polls')}</Form.Label>
							<Form.Select style={{ width: '100%' }} defaultValue={props.attributes.question} onChange={selectPoll} aria-label="Default select example">
								<option disabled >Select question</option>
								{props.attributes.polls.length && props.attributes.polls.map( poll => {
									return  <option value={poll.id}> {poll.question} </option>
								}) }
							</Form.Select>
						</Form.Group>
						<Form.Group style={props.attributes.panelCSS.item}>
							<Form.Label>{__('Add Custom Class')}</Form.Label>
							<Form.Control style={{ width: '100%' }} as="textarea" rows="2" placeholder='space separated' name="address" value={props.attributes.customclass} onChange={setCustomClass} />
						</Form.Group>
						<Form.Group style={props.attributes.panelCSS.item}>
							<Form.Label>{__('Add Custom CSS')}</Form.Label>
							<Form.Control style={{ width: '100%' }} as="textarea" rows="3" placeholder='selector .poll_system_block' name="address" value={props.attributes.customcss} onChange={setCustomCSS} />
						</Form.Group>
					</Form>
				</PanelBody >
			</InspectorControls >,
			<style dangerouslySetInnerHTML={{__html: props.attributes.customcss}}>

			</style>,
			<div className={'poll_system_block '+  props.attributes.customclass}>
				<Form id='poll_form'>
					<Form.Group className='' controlId='poll.question'>
						<div>
							<Form.Label>{__('Add Question')}</Form.Label>
						</div>
						<div className='poll_question'>
							<Form.Control
								type='text'
								name='question'
								style={{ width: '100%' }}
								onChange={setQuestion}
								value={props.attributes.question}
								placeholder='question'
							/>
						</div>
						<div className='poll_answers'>
							{props.attributes.answers.length &&
								props.attributes.answers.map((answer) => (
									<Form.Check 
										inline
										type={'radio'}
										id={`${answer}`}
										label={answer}
										value={answer}
									/>
								))}
						</div>
					</Form.Group>
				</Form>
		</div>
		]
	);
}
