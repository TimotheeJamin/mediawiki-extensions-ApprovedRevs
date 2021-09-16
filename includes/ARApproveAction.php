<?php
/**
 * Handles the 'approve' action.
 *
 * @author Yaron Koren
 * @ingroup ApprovedRevs
 */

class ARApproveAction extends Action {

	/**
	 * Return the name of the action this object responds to
	 * @return string lowercase
	 */
	public function getName() {
		return 'approve';
	}

	/**
	 * The main action entry point. Do all output for display and send it
	 * to the context output.
	 * $this->getOutput(), etc.
	 */
	public function show() {
		$title = $this->getTitle();
		if ( !ApprovedRevs::pageIsApprovable( $title ) ) {
			return true;
		}

		$user = $this->getUser();
		if ( !ApprovedRevs::userCanApprove( $user, $title ) ) {
			return true;
		}
		$request = $this->getRequest();
		if ( !$request->getCheck( 'oldid' ) ) {
			return true;
		}
		$revisionID = $request->getVal( 'oldid' );
		ApprovedRevs::setApprovedRevID( $title, $revisionID, $user );

		$out = $this->getOutput();
		$out->addHTML( "\t\t" . Xml::tags( // [Xml::element does not allow nesting another Xml::element -- TJ]
			'div',
			[ 'class' => 'successbox' ],
			wfMessage( 'approvedrevs-approvesuccess' )->text()
			. ' ' . Xml::element( 'a', [ 'href' => $title->getLocalUrl() ], // [Add link to page's main URL -- TJ]
			wfMessage( 'approvedrevs-viewdefaultpage' )->text()
			)
		) . "\n" );
		$out->addHTML( "\t\t" . Xml::element(
			'p',
			[ 'style' => 'clear: both' ]
		) . "\n" );

		// The purge seems to be needed when the latest version is
		// approved - at least when the Cargo extension is being used.
		if ( method_exists( $this, 'getWikiPage' ) ) {
			// MW 1.35+
			$this->getWikiPage()->doPurge();
			$this->getArticle()->view();
		} else {
			$this->page->doPurge();
			$this->page->view();
		}
	}

}
