class EditUserPage extends PageBase {
	const name = 'user';
	const maintainPageState = true;

	constructor(viewModel) {
		super(viewModel, 'Edit User');
	}

	modelMethod = (id, callback) => {
		if (id) {
			this.userConnector.get(id, callback);
		} else {
			callback(null);
		}
	};

	viewMethod = (data) => {
		if (data) {
			this.userToUpdate = ko.mapping.fromJS(data);
		} else {
			this.userToUpdate = new UserViewModel();
		}
		return this.userToUpdate;
	};
}