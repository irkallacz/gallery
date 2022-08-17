const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
	entry: {
		photo: './www/js/app.photo.js',
	},
	output: {
		filename: 'app.[name].bundle.js',
		path: path.join(__dirname, 'www', 'assets')
	},
	mode: (process.env.NODE_ENV === 'production') ? 'production' : 'development',
	//resolve: {
	//	extensions: ['*', '.js', '.jsx']
	//}
	plugins: [
		new MiniCssExtractPlugin({
			filename: "app.[name].bundle.css",
		}),
	],
	module: {
		rules: [
			{
				test: /\.css$/,
				use: [
					{loader: MiniCssExtractPlugin.loader, options: { publicPath: path.join(__dirname, 'www', 'assets')}},
					//'style-loader',
					'css-loader'
				]
			}
		]
	}
};