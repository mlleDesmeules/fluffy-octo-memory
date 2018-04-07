import { Component, OnInit } from "@angular/core";
import { ActivatedRoute } from "@angular/router";
import { FormArray, FormBuilder, FormGroup, Validators } from "@angular/forms";

import { Category, CategoryService } from "@core/data/categories";
import { Lang } from "@core/data/languages";
import { LoggerService } from "@shared/logger/logger.service";
import { ErrorResponse } from "@core/data/error-response.model";
import { AtIndexOfPipe } from "@shared/pipes/array/at-index-of.pipe";
import { SlugPipe } from "@shared/pipes/string/slug.pipe";

@Component({
	selector    : "app-category-detail",
	templateUrl : "./detail.component.html",
	styleUrls   : [ "./detail.component.scss" ],
})
export class DetailComponent implements OnInit {

	public category: Category;
	public languages: Lang[];

	public form: FormGroup;
	public errors: any = {};
	public loading     = false;

	constructor (private _route: ActivatedRoute,
				 private _builder: FormBuilder,
				 private atIndexOf: AtIndexOfPipe,
				 private slugPipe: SlugPipe,
				 private service: CategoryService,
				 private logger: LoggerService) {
	}

	ngOnInit () {
		this._setData();
		this._createForm();
	}

	/**
	 * Create the Category form with a translation object for each languages available.
	 *
	 * @private
	 */
	private _createForm () {
		this.form = this._builder.group({
			is_active    : this._builder.control(this.category.is_active, [ Validators.required ]),
			translations : this._builder.array([]),
		});

		this.languages.forEach(( val ) => {
			const translation = this.category.findTranslation(val.icu);
			const control     = this._builder.group({
				lang_id : this._builder.control(val.id, [ Validators.required ]),
				name    : this._builder.control(translation.name, [ Validators.required ]),
				slug    : this._builder.control(translation.slug, [ Validators.required ]),
			});

			control.get("slug").disable();

			this.getTranslations().push(control);
		});
	}

	/**
	 *
	 * @param {number} idx
	 * @param {string} field
	 * @return {any}
	 */
	public getErrors ( idx: number, field: string ) {
		if (!this.errors.hasOwnProperty("translations")) {
			return [];
		}

		if (!this.errors.translations.hasOwnProperty(idx)) {
			return [];
		}

		return this.errors.translations[ idx ][ field ];
	}

	/**
	 * Return the translations from the form as FormArray. (helper since the get is kinda long)
	 *
	 * @return {FormArray}
	 */
	public getTranslations (): FormArray {
		return this.form.get("translations") as FormArray;
	}

	/**
	 * Verify if the current page is the creation page.
	 *
	 * @return {boolean}
	 */
	public isCreate () {
		return (typeof this.category === "undefined" || typeof this.category.id === "undefined");
	}

	/**
	 * Reset the form to all empty values, so another category can easily be created.
	 *
	 * @private
	 */
	private _resetForm () {
		this.form.get("is_active").reset();

		this.languages.forEach(( val, idx ) => {
			this.getTranslations().at(idx).reset();
			this.getTranslations().at(idx).get("lang_id").setValue(val.id);
		});
	}

	/**
	 *
	 */
	public save () {
		this.errors  = [];
		this.loading = true;

		let req  = null;
		let msg  = "Changes to category where correctly saved";
		let body = new Category();
		body     = body.form(this.form.getRawValue());

		if (this.isCreate()) {
			req = this.service.create(body);
			msg = "A new category was successfully created";
		} else {
			req = this.service.update(this.category.id, body);
			msg = `Category #${this.category.id} was successfully updated`;
		}

		req.subscribe(
				(result: any) => {
					console.log(result);
					this.loading = false;

					this.logger.success(msg);

					if (this.isCreate()) {
						this._resetForm();
					}
				},
				(error: ErrorResponse) => {
					this.loading = false;
					this.errors  = error.form_error;

					this.logger.error(error.shortMessage);
				}
		);
	}

	/**
	 * Get the data resolved by the route, then assign it to the right property.
	 *
	 * @private
	 */
	private _setData () {
		const routeLanguages = this._route.snapshot.data[ "languages" ];
		const routeCategory  = this._route.snapshot.data[ "category" ];

		this.languages = (routeLanguages) ? routeLanguages : [];
		this.category  = (routeCategory) ? routeCategory : new Category();
	}

	/**
	 *
	 * @param {number} translationIdx
	 */
	public setSlug ( translationIdx: number ) {
		//  get the current name
		const name = this.getTranslations().at(translationIdx).get("name").value;

		//  transform the name to remove spaces, apostrophe and transform accents
		const slug = this.slugPipe.transform(name);

		this.getTranslations().at(translationIdx).get("slug").setValue(slug);
	}

	/**
	 * Verify if the field passed was touched and is still invalid.
	 *
	 * @param {string} field
	 * @param {FormGroup} translation
	 * @param {number} idx
	 *
	 * @return {boolean}
	 */
	public showError ( field: string, translation?: FormGroup, idx?: number ): boolean {
		if (translation) {
			return ((translation.get(field).touched && translation.get(field).invalid) || this.getErrors(idx, field).length > 0);
		}

		return (this.form.get(field).touched && this.form.get(field).invalid);
	}
}
