import { CategoryLang } from "@core/data/categories/category-lang.model";

/**
 *
 */
export class Category {
	public id: number;
	public is_active: boolean;
	public translations: CategoryLang[];
	public created_on: string;
	public updated_on: string;

	constructor ( model: any = null ) {
		if (!model) { return; }

		this.id         = model.id;
		this.is_active  = model.is_active;
		this.created_on = model.created_on;
		this.updated_on = model.updated_on;

		this.translations = this.mapTranslations(model.translations);
	}

	/**
	 *
	 * @param list
	 * @return {CategoryLang[]}
	 */
	mapTranslations ( list: any ): CategoryLang[] {
		list.forEach(( val, idx ) => {
			list[ idx ] = this.translationModel(val);
		});

		return list;
	}

	/**
	 *
	 * @param model
	 * @return {CategoryLang}
	 */
	translationModel ( model: any ): CategoryLang {
		return new CategoryLang(model);
	}

	/**
	 *
	 * @param model
	 */
	form ( model: any ): Category {
		this.is_active = model.is_active;

		this.translations = this.mapFormTranslations(model.translations);

		return this;
	}

	/**
	 *
	 * @param list
	 * @return {CategoryLang[]}
	 */
	mapFormTranslations ( list: any ): CategoryLang[] {
		let result: CategoryLang[] = [];

		list.forEach(( val, idx ) => {
			if (val.name || val.slug) {
				result.push(this.translationModel(val));
			}
		});

		return result;
	}
}
