import { HttpClient } from "@angular/common/http";
import { Inject, Injectable } from "@angular/core";
import { BaseService } from "@core/data/base.service";
import { UserProfile } from "@core/data/users/user-profile.model";

@Injectable()
export class UserProfileService extends BaseService {
	public baseUrl   = "user";
	public modelName = "me";

	constructor (@Inject(HttpClient) http: HttpClient) {
		super(http);

		this.model = ( construct: any ) => new UserProfile(construct);
	}

	public update (body: any) {
		return this.http.put(this._url(), body)
					.toPromise()
					.then(this._parseResponseBody)
					.catch(this._parseErrorBody);
	}

	// TODO implement update password
	// TODO implement upload picture
}
