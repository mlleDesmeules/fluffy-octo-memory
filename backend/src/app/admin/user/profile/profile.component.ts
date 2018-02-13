import { Component, OnInit } from "@angular/core";
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { ActivatedRoute } from "@angular/router";

import { User } from "@core/data/users/user.model";
import { UserProfile } from "@core/data/users/user-profile.model";
import { UserProfileService } from "@core/data/users/user-profile.service";
import { ErrorResponse } from "@core/data/error-response.model";

@Component({
	selector    : "ngx-user-profile",
	templateUrl : "./profile.component.html",
	styleUrls   : [ "./profile.component.scss" ],
})
export class ProfileComponent implements OnInit {

	public form: FormGroup;
	public user: User;

	constructor ( private _route: ActivatedRoute,
				  private _builder: FormBuilder,
				  private service: UserProfileService ) {}

	ngOnInit () {
		this.user = this._route.snapshot.data[ "user" ];

		this._createForm();
	}

	/**
	 * Create the Form group to be used in the profile page.
	 *
	 * @private
	 */
	private _createForm () {
		this.form = this._builder.group({
			firstname : this._builder.control(this.user.profile.firstname, [ Validators.required ]),
			lastname  : this._builder.control(this.user.profile.lastname, [ Validators.required ]),
			birthday  : this._builder.control(this.user.profile.birthdayToDatepicker(), [ Validators.required ]),
		});
	}

	public save () {
		const body = new UserProfile(this.form.getRawValue());

		this.service
			.update(body.form())
			.then((result: any) => {
				console.log(result);
			})
			.catch((error: ErrorResponse) => {
				console.log(error);
			});
	}
}
